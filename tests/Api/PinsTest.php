<?php

namespace seregazhuk\tests\Api;

use seregazhuk\PinterestBot\Helpers\UrlBuilder;
use seregazhuk\PinterestBot\Api\Providers\Pins;

/**
 * Class PinsTest.
 */
class PinsTest extends ProviderTest
{
    /**
     * @var Pins
     */
    protected $provider;

    /**
     * @var string
     */
    protected $providerClass = Pins::class;

    /** @test */
    public function it_should_like_pins()
    {
        $this->setSuccessResponse();
        $this->assertTrue($this->provider->like(1111));

        $this->setErrorResponse();
        $this->assertFalse($this->provider->like(1111));
    }

    /** @test */
    public function it_should_unlike_pins()
    {
        $this->setSuccessResponse();
        $this->assertTrue($this->provider->unLike(1111));

        $this->setErrorResponse();
        $this->assertFalse($this->provider->unLike(1111));
    }

    /** @test */
    public function it_should_create_comments_for_pin()
    {
        $this->setSuccessResponse();
        $this->assertNotEmpty($this->provider->comment(1111, 'comment text'));

        $this->setErrorResponse();
        $this->assertFalse($this->provider->comment(1111, 'comment text'));
    }

    /** @test */
    public function it_should_delete_comments_for_pin()
    {
        $this->setSuccessResponse();
        $this->assertTrue($this->provider->deleteComment(1111, 1111));

        $this->setErrorResponse();
        $this->assertFalse($this->provider->deleteComment(1111, 1111));
    }

    /** @test */
    public function it_should_create_new_pin()
    {
        $this->apiShouldCreatePin();

        $pinSource = 'http://example.com/image.jpg';
        $pinDescription = 'Pin Description';
        $boardId = 1;
        $this->assertNotEmpty($this->provider->create($pinSource, $boardId, $pinDescription));

        $this->setErrorResponse();
        $this->assertEmpty($this->provider->create($pinSource, $boardId, $pinDescription));
    }

    /** @test */
    public function it_should_upload_images_when_creating_pin_with_local_image()
    {
        $image = 'image.jpg';
        $this->request
            ->shouldReceive('upload')
            ->withArgs([$image, UrlBuilder::IMAGE_UPLOAD])
            ->andReturn(json_encode([
                'success' => true,
                'image_url' => 'http://example.com/example.jpg'
            ]));

        $this->apiShouldCreatePin();
        $this->provider->create($image, 1, 'test');
    }

    /** @test */
    public function it_should_create_repin()
    {
        $this->apiShouldCreatePin();

        $boardId = 1;
        $repinId = 11;
        $pinDescription = 'Pin Description';

        $this->assertNotEmpty($this->provider->repin($repinId, $boardId, $pinDescription));
        
        $this->setErrorResponse();
        $this->assertEmpty($this->provider->repin($repinId, $boardId, $pinDescription));
    }

    /** @test */
    public function it_should_edit_pins()
    {
        $this->apiShouldReturnSuccess()
            ->assertTrue($this->provider->edit(1, 'new', 'changed'));

        $this->apiShouldReturnError()
            ->assertFalse($this->provider->edit(1, 'new', 'changed'));
    }

    /** @test */
    public function it_should_delete_pin()
    {
        $this->apiShouldReturnSuccess()
            ->assertTrue($this->provider->delete(1));

        $this->apiShouldReturnError()
            ->assertFalse($this->provider->delete(1));
    }

    /** @test */
    public function it_should_return_pin_info()
    {
        $this->apiShouldReturnSuccess()
            ->assertNotEmpty($this->provider->info(1));

        $this->apiShouldReturnError()
            ->assertEmpty($this->provider->info(1));
    }

    /** @test */
    public function it_should_return_generator_when_searching()
    {
        $response['module']['tree']['data']['results'] = [
            ['id' => 1],
            ['id' => 2],
        ];

        $expectedResultsNum = count($response['module']['tree']['data']['results']);
        $this->apiShouldReturn($response);

        $res = iterator_to_array($this->provider->search('dogs', 2));
        $this->assertCount($expectedResultsNum, $res);
    }

    /** @test */
    public function it_should_move_pins_between_boards()
    {
        $this->setSuccessResponse();
        $this->assertTrue($this->provider->moveToBoard(1111, 1));

        $this->setErrorResponse();
        $this->assertFalse($this->provider->moveToBoard(1111, 1));
    }

    /** @test */
    public function it_should_return_generator_with_pins_for_specific_site()
    {
        $this->apiShouldReturnPagination()
            ->apiShouldReturnEmpty();

        $pins = $this->provider->fromSource('flickr.ru');
        $this->assertCount(2, iterator_to_array($pins));
    }

    /** @test */
    public function it_should_return_generator_with_pin_activity()
    {
        $pinData = ['aggregated_pin_data' => ['id' => 1]];
        $this->apiShouldReturnData($pinData)
            ->apiShouldReturnPagination()
            ->apiShouldReturnEmpty()
            ->assertCount(2, iterator_to_array($this->provider->activity(1)));
    }

    /** @test */
    public function it_should_return_null_for_empty_activity()
    {
        $this->apiShouldReturnSuccess()
            ->assertNull($this->provider->activity(1));
    }

    /** @test */
    public function it_should_return_generator_for_users_feed()
    {
        $this->apiShouldReturnPagination()
            ->apiShouldReturnEmpty();

        $res = iterator_to_array($this->provider->userFeed());
        $this->assertCount(2, $res);
    }

    /** @test */
    public function it_should_return_generator_for_related_pins()
    {
        $this->apiShouldReturnPagination()
            ->apiShouldReturnEmpty();

        $pinId = 1;
        $res = iterator_to_array($this->provider->getRelatedPins($pinId));
        $this->assertCount(2, $res);
    }


    /**
     * Creates a pin creation response from Pinterest.
     *
     * @return $this
     */
    protected function apiShouldCreatePin()
    {
        $data = ['id' => 1];

        return $this->apiShouldReturnData($data);
    }
}
