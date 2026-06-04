<?php

namespace Tests\Feature\Sales;

use App\Sales\Infrastructure\Persistence\Models\EloquentSales;
use App\Sales\Infrastructure\Persistence\Repositories\EloquentSalesRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NextTicketNumberTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function next_ticket_number_uses_the_highest_ticket_even_after_cancelation(): void
    {
        EloquentSales::factory()->create([
            'ticket_number' => 19,
        ]);

        $cancelledSale = EloquentSales::factory()->create([
            'ticket_number' => 20,
        ]);

        $cancelledSale->delete();

        $repository = app(EloquentSalesRepository::class);

        $this->assertSame(21, $repository->nextTicketNumber());
        $this->assertSoftDeleted('sales', ['id' => $cancelledSale->id]);
    }
}
