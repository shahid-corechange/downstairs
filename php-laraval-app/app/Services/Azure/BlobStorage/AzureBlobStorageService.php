<?php

namespace App\Services\Azure\BlobStorage;

use App\Contracts\StorageService;
use App\Enums\Azure\BlobStorage\BlobStorageUploadSourceEnum;
use App\Exceptions\Azure\InvalidConnectionStringException;
use App\Exceptions\OperationFailedException;
use Http;
use Illuminate\Http\Client\Response as ClientResponse;
use Illuminate\Http\Response;
use Storage;
use Str;

class AzureBlobStorageService implements StorageService
{
    /**
     * Constant for the blob storage API version.
     *
     * @var string
     */
    const API_VERSION = '2017-11-09';

    /**
     * The account name.
     */
    protected string $accountName;

    /**
     * The account key.
     */
    protected string $accountKey;

    /**
     * http/https protocol.
     */
    protected string $protocol;

    /**
     * Blob storage domain suffix.
     */
    protected string $endpointSuffix;

    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $config = $this->parseConnectionString();

        $this->accountName = $config['accountName'];
        $this->accountKey = $config['accountKey'];
        $this->protocol = $config['defaultEndpointsProtocol'];
        $this->endpointSuffix = $config['endpointSuffix'];
    }

    /**
     * Read the connection string config and extract it.
     *
     * @return string[]
     *
     * @throws InvalidConnectionStringException
     */
    private function parseConnectionString(): array
    {
        $connectionString = config('services.azure_storage.connection_string');

        if (! $connectionString) {
            throw new InvalidConnectionStringException(__('missing connection string'));
        }

        $properties = explode(';', $connectionString);
        $config = array_reduce($properties, function ($accumulator, $property) {
            $parts = explode('=', $property, 2);

            if (count($parts) !== 2) {
                throw new InvalidConnectionStringException(__('invalid value for', ['value' => $parts[0]]));
            }

            $accumulator[Str::camel($parts[0])] = $parts[1];

            return $accumulator;
        }, []);

        $config = array_merge([
            'defaultEndpointsProtocol' => 'https',
            'endpointSuffix' => 'core.windows.net',
        ], $config);

        if (! isset($config['accountName'])) {
            throw new InvalidConnectionStringException(__('missing the storage account name'));
        }

        if (! isset($config['accountKey'])) {
            throw new InvalidConnectionStringException(__('missing the storage account key'));
        }

        return $config;
    }

    /**
     * Create canonicalized headers from x-ms headers.
     *
     * @param  string[]  $headers
     */
    private function getCanonicalizedHeaders(array $headers): string
    {
        ksort($headers);

        return array_reduce(
            array_keys($headers),
            function ($accumulator, $key) use ($headers) {
                if (str_starts_with($key, 'x-ms')) {
                    $accumulator .= $key.':'.$headers[$key]."\n";
                }

                return $accumulator;
            },
            ''
        );
    }

    /**
     * Create canonicalized resource based on URL path and query string.
     */
    private function getCanonicalizedResource(string $uri): string
    {
        // Split the URI into its endpoint and query string components
        $uriParts = explode('?', $uri, 2);
        $endpoint = $uriParts[0];
        $query = '';

        // If a query string exists, sort and concatenate each query parameter
        if (count($uriParts) === 2) {
            $queries = explode('&', $uriParts[1]);
            $query = array_reduce($queries, function ($accumulator, $item) {
                $queryItems = explode('=', $item, 2);

                if (count($queryItems) === 2) {
                    $accumulator .= "\n".Str::lower($queryItems[0]).':'.$queryItems[1];
                }

                return $accumulator;
            }, '');
        }

        // Format the URI into our expected format
        return sprintf('/%s/%s%s', $this->accountName, $endpoint, $query);
    }

    /**
     * Create the shared key from the signature for Authorization.
     *
     * @param  string[]  $headers
     */
    private function createAuthorizationHeader(
        string $uri,
        string $method,
        array $headers
    ): string {
        $contentEncoding = isset($headers['Content-Encoding']) ? $headers['Content-Encoding'] : '';
        $contentLanguage = isset($headers['Content-Language']) ? $headers['Content-Language'] : '';
        $contentLength = isset($headers['Content-Length']) ? $headers['Content-Length'] : '';
        $contentLength = $contentLength ?: '';
        $contentMD5 = isset($headers['Content-MD5']) ? $headers['Content-MD5'] : '';
        $contentType = isset($headers['Content-Type']) ? $headers['Content-Type'] : '';
        $date = isset($headers['Date']) ? $headers['Date'] : '';
        $ifModifiedSince = isset($headers['If-Modified-Since']) ? $headers['If-Modified-Since'] : '';
        $ifMatch = isset($headers['If-Match']) ? $headers['If-Match'] : '';
        $ifNoneMatch = isset($headers['If-None-Match']) ? $headers['If-None-Match'] : '';
        $ifUnmodifiedSince = isset($headers['If-Unmodified-Since']) ? $headers['If-Unmodified-Since'] : '';
        $range = isset($headers['Range']) ? $headers['Range'] : '';

        $canonicalizedHeaders = $this->getCanonicalizedHeaders($headers);
        $canonicalizedResource = $this->getCanonicalizedResource($uri);
        $stringToSign = $method."\n"
                        .$contentEncoding."\n"
                        .$contentLanguage."\n"
                        .$contentLength."\n"
                        .$contentMD5."\n"
                        .$contentType."\n"
                        .$date."\n"
                        .$ifModifiedSince."\n"
                        .$ifMatch."\n"
                        .$ifNoneMatch."\n"
                        .$ifUnmodifiedSince."\n"
                        .$range."\n"
                        .$canonicalizedHeaders
                        .$canonicalizedResource;

        $signature = base64_encode(
            hash_hmac(
                'sha256',
                $stringToSign,
                base64_decode($this->accountKey),
                true
            )
        );

        return 'SharedKey '.$this->accountName.':'.$signature;
    }

    /**
     * Send authenticated request to Azure Blob Storage REST API.
     *
     * @param  string[]  $headers
     */
    private function sendRequest(
        string $endpoint,
        string $method,
        string $body = '',
        array $headers = []
    ): ClientResponse {
        $url = sprintf(
            '%s://%s.blob.%s/%s',
            $this->protocol,
            $this->accountName,
            $this->endpointSuffix,
            $endpoint
        );

        $headers['x-ms-date'] = now('GMT')->format('D, d M Y H:i:s T');
        $headers['x-ms-version'] = $this::API_VERSION;
        $headers['Authorization'] = $this->createAuthorizationHeader($endpoint, $method, $headers);

        $options = ['headers' => $headers];

        if ($body) {
            $options['body'] = $body;
        }

        $response = Http::send($method, $url, $options);

        return $response;
    }

    /**
     * Get content and metadata of a file.
     *
     * @return string[]
     *
     * @throws OperationFailedException
     */
    public function download(string $container, string $fileName): array
    {
        $response = $this->sendRequest("$container/$fileName", 'GET');

        if ($response->status() !== Response::HTTP_OK) {
            throw new OperationFailedException(__('failed to download file', ['reason' => $response->reason()]), $response->status());
        }

        return [
            'headers' => [
                'Content-Length' => $response->header('Content-Length'),
                'Content-Type' => $response->header('Content-Type'),
                'ETag' => $response->header('ETag'),
                'Last-Modified' => $response->header('Last-Modified'),
            ],
            'body' => $response->body(),
        ];
    }

    /**
     * Upload a file to the storage, overwrite the existing file.
     *
     * @throws OperationFailedException
     */
    public function upload(string $container, string $source, string $fileKeyOrPath, string $fileName): ?string
    {
        if ($source === BlobStorageUploadSourceEnum::Local()) {
            $storage = Storage::disk('storage');

            if (! $storage->exists($fileKeyOrPath)) {
                return null;
            }

            $fileContents = $storage->get($fileKeyOrPath);
            $fileSize = $storage->size($fileKeyOrPath);
            $fileMimeType = $storage->mimeType($fileKeyOrPath);
        } else {
            $file = request()->file($fileKeyOrPath);

            if (! $file) {
                return null;
            } elseif (is_array($file)) {
                $file = $file[0];
            }

            $fileContents = $file->get();
            $fileSize = $file->getSize();
            $fileMimeType = $file->getMimeType();
        }

        $headers = [
            'Content-Length' => $fileSize,
            'Content-Type' => $fileMimeType,
            'Content-MD5' => base64_encode(md5($fileContents, true)),
            'x-ms-blob-type' => 'BlockBlob',
        ];

        $response = $this->sendRequest("$container/$fileName", 'PUT', $fileContents, $headers);

        if ($response->status() !== Response::HTTP_CREATED) {
            throw new OperationFailedException(__('failed to upload file', ['reason' => $response->reason()]), $response->status());
        }

        // return the file url
        return sprintf(
            '%s://%s.blob.%s/%s/%s',
            $this->protocol,
            $this->accountName,
            $this->endpointSuffix,
            $container,
            $fileName
        );
    }

    /**
     * Delete a file from the storage.
     *
     * @throws OperationFailedException
     */
    public function delete(string $container, string $fileName): void
    {
        $response = $this->sendRequest("$container/$fileName", 'DELETE');

        if ($response->status() !== Response::HTTP_ACCEPTED) {
            throw new OperationFailedException(__('failed to delete file', ['reason' => $response->reason()]), $response->status());
        }
    }
}
