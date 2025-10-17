import { Box, BoxProps } from "@chakra-ui/react";
import { useSize } from "@chakra-ui/react-use-size";
import { LatLngLiteral, Map as LeafletMap } from "leaflet";
import "leaflet/dist/leaflet.css";
import { useEffect, useRef } from "react";
import { MapContainer, MapContainerProps, TileLayer } from "react-leaflet";

import Marker, { MarkerProps } from "./components/Marker";
import Recenter from "./components/Recenter";

interface MapProps extends BoxProps {
  center?: LatLngLiteral;
  zoom?: number;
  mapContainer?: MapContainerProps;
  markers?: MarkerProps[];
  onMarkerMove?: (position: LatLngLiteral, index: number) => void;
}

const Map = ({
  mapContainer,
  onMarkerMove,
  center = { lat: 51.505, lng: -0.09 },
  zoom = 13,
  markers = [],
  ...props
}: MapProps) => {
  const containerRef = useRef<HTMLDivElement>(null);
  const mapRef = useRef<LeafletMap>(null);
  const containerSize = useSize(containerRef);

  useEffect(() => {
    if (mapRef.current) {
      mapRef.current.invalidateSize();
    }
  }, [containerSize]);

  return (
    <Box
      ref={containerRef}
      h={400}
      display="flex"
      overflow="hidden"
      zIndex={0}
      {...props}
    >
      <MapContainer
        ref={mapRef}
        center={center}
        zoom={zoom}
        style={{ flex: 1, width: "100%" }}
        {...mapContainer}
      >
        <TileLayer
          attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
          url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
        />
        <Recenter latitude={center.lat} longitude={center.lng} />
        {markers.map((marker, index) => (
          <Marker
            key={index}
            {...marker}
            onDragEnd={(position) => onMarkerMove?.(position, index)}
          />
        ))}
      </MapContainer>
    </Box>
  );
};

export default Map;
