<?php

namespace Tests\Feature;

use App\Models\Estimate;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MeasurementEstimateControllerTest extends TestCase
{
    use DatabaseTransactions;

    private function makeEstimate(): Estimate
    {
        return Estimate::create(['subject' => 'Measurement test estimate', 'bill_date' => now()->toDateString(), 'total' => 0]);
    }

    public function test_a_user_without_edit_measurement_access_is_forbidden(): void
    {
        $user = User::factory()->subAdmin(['Estimate'])->create();
        $estimate = $this->makeEstimate();

        $this->actingAs($user)->get("/estimates/{$estimate->id}/measurement")->assertForbidden();
    }

    public function test_it_saves_groups_and_lines_replacing_prior_data(): void
    {
        $user = User::factory()->subAdmin(['Edit Measurement'])->create();
        $estimate = $this->makeEstimate();

        $response = $this->actingAs($user)->put("/estimates/{$estimate->id}/measurement", [
            'groups' => [
                [
                    'total' => '6', 'total_text' => '', 'total_unit' => 'MTR',
                    'lines' => [
                        ['service_no' => 'SVC1', 'description' => 'Cable run', 'no' => '2', 'length' => '3', 'breath' => '1', 'unit' => '1', 'quantity' => '6.000'],
                    ],
                ],
            ],
        ]);

        $response->assertRedirect(route('estimates.measurement.edit', $estimate));
        $estimate->refresh();
        $this->assertCount(1, $estimate->measurementItems);
        $this->assertSame('Cable run', $estimate->measurementItems->first()->lines->first()->description);
    }

    public function test_print_requires_its_own_permission(): void
    {
        $user = User::factory()->subAdmin(['Edit Measurement'])->create();
        $estimate = $this->makeEstimate();

        $this->actingAs($user)->get("/estimates/{$estimate->id}/measurement/print")->assertForbidden();

        $printUser = User::factory()->subAdmin(['Print Estimate Measurement'])->create();
        $response = $this->actingAs($printUser)->get("/estimates/{$estimate->id}/measurement/print");
        $response->assertOk();
        $response->assertSee('Measurement Sheet');
    }
}
