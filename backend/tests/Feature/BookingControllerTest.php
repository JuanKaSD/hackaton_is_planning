<?php

namespace Tests\Feature;

use App\Models\Airline;
use App\Models\Booking;
use App\Models\Flight;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;

class BookingControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test booking flights with 24+ hours advance.
     */
    public function test_client_can_book_flight_with_proper_advance_notice(): void
    {
        // Create a client user
        $user = User::factory()->client()->create();
        
        // Create airline and flight that departs in 2 days
        $airline = Airline::factory()->create();
        $flight = Flight::factory()->create([
            'airline_id' => $airline->id,
            'flight_date' => Carbon::now()->addDays(2),
            'duration' => 120, // 2 hours
            'passenger_capacity' => 100
        ]);
        
        // Attempt to book the flight
        $response = $this->actingAs($user, 'sanctum')
                         ->postJson('/api/bookings', [
                             'flight_id' => $flight->id
                         ]);
        
        $response->assertStatus(201)
                 ->assertJson(['message' => 'Flight booked successfully']);
                 
        // Verify booking was created
        $this->assertDatabaseHas('bookings', [
            'user_id' => $user->id,
            'flight_id' => $flight->id,
            'status' => 'confirmed'
        ]);
    }
    
    /**
     * Test that booking fails if flight is within 24 hours.
     */
    public function test_client_cannot_book_flight_within_24_hours(): void
    {
        // Create a client user
        $user = User::factory()->client()->create();
        
        // Create airline and flight that departs in 12 hours
        $airline = Airline::factory()->create();
        $flight = Flight::factory()->create([
            'airline_id' => $airline->id,
            'flight_date' => Carbon::now()->addHours(12),
            'duration' => 120, // 2 hours
            'passenger_capacity' => 100
        ]);
        
        // Attempt to book the flight
        $response = $this->actingAs($user, 'sanctum')
                         ->postJson('/api/bookings', [
                             'flight_id' => $flight->id
                         ]);
        
        $response->assertStatus(400)
                 ->assertJson(['message' => 'Bookings must be made at least 24 hours before departure']);
                 
        // Verify no booking was created
        $this->assertDatabaseMissing('bookings', [
            'user_id' => $user->id,
            'flight_id' => $flight->id
        ]);
    }
    
    /**
     * Test that client cannot book overlapping flights.
     */
    public function test_client_cannot_book_overlapping_flights(): void
    {
        // Create a client user
        $user = User::factory()->client()->create();
        
        // Create airline
        $airline = Airline::factory()->create();
        
        // Create flight 1 (lasts from 10:00 to 12:00 in 3 days)
        $flight1 = Flight::factory()->create([
            'airline_id' => $airline->id,
            'flight_date' => Carbon::now()->addDays(3)->setTime(10, 0),
            'duration' => 120, // 2 hours
            'passenger_capacity' => 100
        ]);
        
        // Book flight 1
        Booking::create([
            'user_id' => $user->id,
            'flight_id' => $flight1->id,
            'status' => 'confirmed',
            'booking_reference' => 'REF' . rand(10000, 99999)
        ]);
        
        // Create flight 2 (lasts from 11:00 to 13:00 in 3 days - overlaps with flight 1)
        $flight2 = Flight::factory()->create([
            'airline_id' => $airline->id,
            'flight_date' => Carbon::now()->addDays(3)->setTime(11, 0),
            'duration' => 120, // 2 hours
            'passenger_capacity' => 100
        ]);
        
        // Attempt to book flight 2
        $response = $this->actingAs($user, 'sanctum')
                         ->postJson('/api/bookings', [
                             'flight_id' => $flight2->id
                         ]);
        
        $response->assertStatus(400)
                 ->assertJson(['message' => 'You already have a booking for a flight that overlaps with this one']);
                 
        // Verify second booking was not created
        $this->assertDatabaseMissing('bookings', [
            'user_id' => $user->id,
            'flight_id' => $flight2->id
        ]);
    }
    
    /**
     * Test that client can book non-overlapping flights.
     */
    public function test_client_can_book_non_overlapping_flights(): void
    {
        // Create a client user
        $user = User::factory()->client()->create();
        
        // Create airline
        $airline = Airline::factory()->create();
        
        // Create flight 1 (lasts from 10:00 to 12:00 in 3 days)
        $flight1 = Flight::factory()->create([
            'airline_id' => $airline->id,
            'flight_date' => Carbon::now()->addDays(3)->setTime(10, 0),
            'duration' => 120, // 2 hours
            'passenger_capacity' => 100
        ]);
        
        // Book flight 1
        Booking::create([
            'user_id' => $user->id,
            'flight_id' => $flight1->id,
            'status' => 'confirmed',
            'booking_reference' => 'REF' . rand(10000, 99999)
        ]);
        
        // Create flight 2 (lasts from 14:00 to 16:00 in 3 days - doesn't overlap with flight 1)
        $flight2 = Flight::factory()->create([
            'airline_id' => $airline->id,
            'flight_date' => Carbon::now()->addDays(3)->setTime(14, 0),
            'duration' => 120, // 2 hours
            'passenger_capacity' => 100
        ]);
        
        // Attempt to book flight 2
        $response = $this->actingAs($user, 'sanctum')
                         ->postJson('/api/bookings', [
                             'flight_id' => $flight2->id
                         ]);
        
        $response->assertStatus(201)
                 ->assertJson(['message' => 'Flight booked successfully']);
                 
        // Verify second booking was created
        $this->assertDatabaseHas('bookings', [
            'user_id' => $user->id,
            'flight_id' => $flight2->id,
            'status' => 'confirmed'
        ]);
    }
}
