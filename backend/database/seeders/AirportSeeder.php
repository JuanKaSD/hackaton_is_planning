<?php

namespace Database\Seeders;

use App\Models\Airport;
use Illuminate\Database\Seeder;

class AirportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $airports = [
            // North America
            ['id' => 'ATL', 'name' => 'Hartsfield-Jackson Atlanta International Airport', 'country' => 'United States'],
            ['id' => 'LAX', 'name' => 'Los Angeles International Airport', 'country' => 'United States'],
            ['id' => 'ORD', 'name' => 'O\'Hare International Airport', 'country' => 'United States'],
            ['id' => 'DFW', 'name' => 'Dallas/Fort Worth International Airport', 'country' => 'United States'],
            ['id' => 'JFK', 'name' => 'John F. Kennedy International Airport', 'country' => 'United States'],
            ['id' => 'DEN', 'name' => 'Denver International Airport', 'country' => 'United States'],
            ['id' => 'SFO', 'name' => 'San Francisco International Airport', 'country' => 'United States'],
            ['id' => 'LAS', 'name' => 'Harry Reid International Airport', 'country' => 'United States'],
            ['id' => 'YYZ', 'name' => 'Toronto Pearson International Airport', 'country' => 'Canada'],
            ['id' => 'YVR', 'name' => 'Vancouver International Airport', 'country' => 'Canada'],
            ['id' => 'MEX', 'name' => 'Mexico City International Airport', 'country' => 'Mexico'],
            
            // Europe
            ['id' => 'LHR', 'name' => 'London Heathrow Airport', 'country' => 'United Kingdom'],
            ['id' => 'CDG', 'name' => 'Paris Charles de Gaulle Airport', 'country' => 'France'],
            ['id' => 'AMS', 'name' => 'Amsterdam Airport Schiphol', 'country' => 'Netherlands'],
            ['id' => 'FRA', 'name' => 'Frankfurt Airport', 'country' => 'Germany'],
            ['id' => 'MAD', 'name' => 'Adolfo Suárez Madrid–Barajas Airport', 'country' => 'Spain'],
            ['id' => 'BCN', 'name' => 'Barcelona–El Prat Airport', 'country' => 'Spain'],
            ['id' => 'FCO', 'name' => 'Leonardo da Vinci International Airport', 'country' => 'Italy'],
            ['id' => 'MUC', 'name' => 'Munich Airport', 'country' => 'Germany'],
            ['id' => 'ZRH', 'name' => 'Zurich Airport', 'country' => 'Switzerland'],
            ['id' => 'IST', 'name' => 'Istanbul Airport', 'country' => 'Turkey'],
            
            // Asia-Pacific
            ['id' => 'HND', 'name' => 'Tokyo Haneda Airport', 'country' => 'Japan'],
            ['id' => 'PEK', 'name' => 'Beijing Capital International Airport', 'country' => 'China'],
            ['id' => 'SIN', 'name' => 'Singapore Changi Airport', 'country' => 'Singapore'],
            ['id' => 'HKG', 'name' => 'Hong Kong International Airport', 'country' => 'China'],
            ['id' => 'ICN', 'name' => 'Incheon International Airport', 'country' => 'South Korea'],
            ['id' => 'BKK', 'name' => 'Suvarnabhumi Airport', 'country' => 'Thailand'],
            ['id' => 'SYD', 'name' => 'Sydney Airport', 'country' => 'Australia'],
            ['id' => 'MEL', 'name' => 'Melbourne Airport', 'country' => 'Australia'],
            ['id' => 'DEL', 'name' => 'Indira Gandhi International Airport', 'country' => 'India'],
            ['id' => 'BOM', 'name' => 'Chhatrapati Shivaji Maharaj International Airport', 'country' => 'India'],
            
            // Middle East
            ['id' => 'DXB', 'name' => 'Dubai International Airport', 'country' => 'United Arab Emirates'],
            ['id' => 'DOH', 'name' => 'Hamad International Airport', 'country' => 'Qatar'],
            ['id' => 'AUH', 'name' => 'Abu Dhabi International Airport', 'country' => 'United Arab Emirates'],
            
            // South America
            ['id' => 'GRU', 'name' => 'São Paulo–Guarulhos International Airport', 'country' => 'Brazil'],
            ['id' => 'EZE', 'name' => 'Ministro Pistarini International Airport', 'country' => 'Argentina'],
            ['id' => 'BOG', 'name' => 'El Dorado International Airport', 'country' => 'Colombia'],
            ['id' => 'SCL', 'name' => 'Santiago International Airport', 'country' => 'Chile'],
            
            // Africa
            ['id' => 'JNB', 'name' => 'O. R. Tambo International Airport', 'country' => 'South Africa'],
            ['id' => 'CAI', 'name' => 'Cairo International Airport', 'country' => 'Egypt'],
            ['id' => 'CPT', 'name' => 'Cape Town International Airport', 'country' => 'South Africa'],
        ];

        foreach ($airports as $airportData) {
            Airport::create($airportData);
        }
    }
}
