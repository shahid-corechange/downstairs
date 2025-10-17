<?php

namespace Tests\Unit;

use App\Services\Azure\NotificationHub\NotificationTagBuilder;
use Tests\TestCase;

class NotificationTagBuilderTest extends TestCase
{
    /**
     * Test tag method.
     *
     * @return void
     */
    public function testTag()
    {
        $tagBuilder = new NotificationTagBuilder('tag1');
        $tagBuilder->tag('tag2');
        $this->assertEquals('tag1 && tag2', $tagBuilder->get());
    }

    /**
     * Test tag method with closure.
     *
     * @return void
     */
    public function testTagWithClosure()
    {
        $tagBuilder = new NotificationTagBuilder('tag1');
        $tagBuilder->tag(function ($tagBuilder) {
            $tagBuilder->tag('tag2');
            $tagBuilder->tag('tag3');
        });
        $this->assertEquals('tag1 && (tag2 && tag3)', $tagBuilder->get());
    }

    /**
     * Test orTag method.
     *
     * @return void
     */
    public function testOrTag()
    {
        $tagBuilder = new NotificationTagBuilder('tag1');
        $tagBuilder->orTag('tag2');
        $this->assertEquals('tag1 || tag2', $tagBuilder->get());
    }

    /**
     * Test orTag method with closure.
     *
     * @return void
     */
    public function testOrTagWithClosure()
    {
        $tagBuilder = new NotificationTagBuilder('tag1');
        $tagBuilder->orTag(function ($tagBuilder) {
            $tagBuilder->tag('tag2');
            $tagBuilder->tag('tag3');
        });
        $this->assertEquals('tag1 || (tag2 && tag3)', $tagBuilder->get());
    }

    /**
     * Test notTag method.
     *
     * @return void
     */
    public function testNotTag()
    {
        $tagBuilder = new NotificationTagBuilder('tag1');
        $tagBuilder->notTag('tag2');
        $this->assertEquals('tag1 && !tag2', $tagBuilder->get());
    }

    /**
     * Test notTag method with closure.
     *
     * @return void
     */
    public function testNotTagWithClosure()
    {
        $tagBuilder = new NotificationTagBuilder('tag1');
        $tagBuilder->notTag(function ($tagBuilder) {
            $tagBuilder->tag('tag2');
            $tagBuilder->tag('tag3');
        });
        $this->assertEquals('tag1 && !(tag2 && tag3)', $tagBuilder->get());
    }

    /**
     * Test tag method with closure and notTag method and orTag method with closure.
     *
     * @return void
     */
    public function testTagWithClosureAndNotTagAndOrTagWithClosure()
    {
        $tagBuilder = new NotificationTagBuilder('tag1');
        $tagBuilder->tag(function ($tagBuilder) {
            $tagBuilder->tag('tag2');
            $tagBuilder->notTag(function ($tagBuilder) {
                $tagBuilder->tag('tag3');
                $tagBuilder->orTag(function ($tagBuilder) {
                    $tagBuilder->tag('tag4');
                    $tagBuilder->tag('tag5');
                });
            });
        });
        $this->assertEquals('tag1 && (tag2 && !(tag3 || (tag4 && tag5)))', $tagBuilder->get());
    }
}
