<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use Mockery;
use Mockery\MockInterface;
use App\Services\RequestService;
use App\Interfaces\{RequestRepositoryInterface,ItemRepositoryInterface,StockMovementRepositoryInterface,};
use App\Models\{ItemRequest, RequestItem, Item, User};
use App\Enums\{RequestStatus, StockMovementType};
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class RequestServiceTest extends TestCase
{
    private RequestService $service;

    /** @var RequestRepositoryInterface&MockInterface */
    private RequestRepositoryInterface $requestRepo;

    /** @var ItemRepositoryInterface&MockInterface */
    private ItemRepositoryInterface $itemRepo;

    /** @var StockMovementRepositoryInterface&MockInterface */
    private StockMovementRepositoryInterface $movementRepo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->requestRepo  = Mockery::mock(RequestRepositoryInterface::class);
        $this->itemRepo     = Mockery::mock(ItemRepositoryInterface::class);
        $this->movementRepo = Mockery::mock(StockMovementRepositoryInterface::class);

        $this->service = new RequestService(
            $this->requestRepo,
            $this->itemRepo,
            $this->movementRepo,
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function mockSpv(int $id = 2): MockInterface
    {
        $spv     = Mockery::mock(User::class);
        $spv->id = $id;
        return $spv;
    }

    private function mockSubmittedRequest(int $id = 1): MockInterface
    {
        $request = Mockery::mock(ItemRequest::class);
        $request->id             = $id;
        $request->request_number = 'REQ-202501-0001';
        $request->shouldReceive('isSubmitted')->andReturn(true);
        return $request;
    }

    private function mockDraftRequest(int $id = 1): MockInterface
    {
        $request = Mockery::mock(ItemRequest::class);
        $request->id = $id;
        $request->shouldReceive('isSubmitted')->andReturn(false);
        return $request;
    }

    private function mockRequestItem(int $id, int $itemId): MockInterface
    {
        $reqItem          = Mockery::mock(RequestItem::class);
        $reqItem->id      = $id;
        $reqItem->item_id = $itemId;
        $reqItem->shouldReceive('update')->andReturn(true);
        return $reqItem;
    }

    private function mockItem(int $id, string $name, int $stock, int $reserved = 0): MockInterface
    {
        $item                  = Mockery::mock(Item::class);
        $item->id              = $id;
        $item->name            = $name;
        $item->stock_quantity  = $stock;
        $item->available_stock = $stock - $reserved;
        return $item;
    }

    /**
     * Mock chain: $request->items()->where('item_id', $itemId)->firstOrFail()
     * Karena items() return type HasMany di-enforce Laravel, kita mock
     * seluruh ItemRequest (bukan makePartial) sehingga return type tidak dicek.
     */
    private function mockItemsChain(MockInterface $request, int $itemId, MockInterface $reqItem): void
    {
        $whereQuery = Mockery::mock();
        $whereQuery->shouldReceive('firstOrFail')->once()->andReturn($reqItem);

        $itemsQuery = Mockery::mock();
        $itemsQuery->shouldReceive('where')->with('item_id', $itemId)->once()->andReturn($whereQuery);

        $request->shouldReceive('items')->once()->andReturn($itemsQuery);
    }

    // ─── Tests ───────────────────────────────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\Test]
    public function approve_berhasil_ketika_stok_mencukupi(): void
    {
        // Arrange
        $spv     = $this->mockSpv();
        $request = $this->mockSubmittedRequest();
        $reqItem = $this->mockRequestItem(id: 5, itemId: 10);
        $item    = $this->mockItem(id: 10, name: 'Kabel UTP', stock: 20);

        $this->mockItemsChain($request, itemId: 10, reqItem: $reqItem);

        DB::shouldReceive('transaction')->once()->andReturnUsing(fn($cb) => $cb());
        $this->requestRepo->shouldReceive('findWithItems')->with(1)->andReturn($request);
        $this->itemRepo->shouldReceive('findById')->with(10)->andReturn($item);
        $this->itemRepo->shouldReceive('decrementStock')->once()->with(10, 3);
        $this->movementRepo->shouldReceive('record')->once()->with(Mockery::on(function ($data) {
            return $data['item_id']      === 10
                && $data['type']         === StockMovementType::OUT
                && $data['quantity']     === -3
                && $data['stock_before'] === 20
                && $data['stock_after']  === 17;
        }));

        $approved         = Mockery::mock(ItemRequest::class);
        $approved->status = RequestStatus::APPROVED;
        $this->requestRepo->shouldReceive('update')->once()->with(1, Mockery::on(
            fn($d) => $d['status'] === RequestStatus::APPROVED && $d['approved_by'] === $spv->id
        ))->andReturn($approved);

        // Act
        $result = $this->service->approve(1, [
            'items' => [['item_id' => 10, 'quantity_approved' => 3]],
        ], $spv);

        // Assert
        $this->assertEquals(RequestStatus::APPROVED, $result->status);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function approve_gagal_ketika_stok_tidak_mencukupi(): void
    {
        // Arrange — stok hanya 2, diminta 5
        $spv     = $this->mockSpv();
        $request = $this->mockSubmittedRequest();
        $reqItem = $this->mockRequestItem(id: 5, itemId: 10);
        $item    = $this->mockItem(id: 10, name: 'Kabel UTP', stock: 2);

        $this->mockItemsChain($request, itemId: 10, reqItem: $reqItem);

        DB::shouldReceive('transaction')->once()->andReturnUsing(fn($cb) => $cb());
        $this->requestRepo->shouldReceive('findWithItems')->with(1)->andReturn($request);
        $this->itemRepo->shouldReceive('findById')->with(10)->andReturn($item);
        $this->itemRepo->shouldNotReceive('decrementStock');
        $this->movementRepo->shouldNotReceive('record');

        // Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Stok Kabel UTP tidak mencukupi');

        // Act
        $this->service->approve(1, [
            'items' => [['item_id' => 10, 'quantity_approved' => 5]],
        ], $spv);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function approve_gagal_ketika_request_bukan_submitted(): void
    {
        // Arrange — request masih Draft
        $spv     = $this->mockSpv();
        $request = $this->mockDraftRequest();

        DB::shouldReceive('transaction')->once()->andReturnUsing(fn($cb) => $cb());
        $this->requestRepo->shouldReceive('findWithItems')->with(1)->andReturn($request);
        $this->itemRepo->shouldNotReceive('findById');
        $this->itemRepo->shouldNotReceive('decrementStock');
        $this->movementRepo->shouldNotReceive('record');

        // Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Hanya request submitted yang bisa diapprove');

        // Act
        $this->service->approve(1, ['items' => []], $spv);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function approve_skip_item_ketika_quantity_approved_nol(): void
    {
        // Arrange — qty_approved = 0, stok tidak boleh disentuh
        $spv     = $this->mockSpv();
        $request = $this->mockSubmittedRequest();
        $reqItem = $this->mockRequestItem(id: 5, itemId: 10);

        $this->mockItemsChain($request, itemId: 10, reqItem: $reqItem);

        DB::shouldReceive('transaction')->once()->andReturnUsing(fn($cb) => $cb());
        $this->requestRepo->shouldReceive('findWithItems')->with(1)->andReturn($request);
        $this->itemRepo->shouldNotReceive('findById');
        $this->itemRepo->shouldNotReceive('decrementStock');
        $this->movementRepo->shouldNotReceive('record');

        $approved         = Mockery::mock(ItemRequest::class);
        $approved->status = RequestStatus::APPROVED;
        $this->requestRepo->shouldReceive('update')->once()->andReturn($approved);

        // Act
        $result = $this->service->approve(1, [
            'items' => [['item_id' => 10, 'quantity_approved' => 0]],
        ], $spv);

        // Assert
        $this->assertEquals(RequestStatus::APPROVED, $result->status);
    }
}
