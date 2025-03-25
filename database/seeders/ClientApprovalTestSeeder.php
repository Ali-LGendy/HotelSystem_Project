<?php

namespace Database\Seeders;

use App\Models\Floor;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class ClientApprovalTestSeeder extends Seeder
{
    /**
     * Run the database seeds to test client approval functionality.
     */
    public function run(): void
    {
        // Make sure roles and permissions are set up
        $this->call(RolesAndPermissionsSeeder::class);

        // Create admin user
        $admin = $this->createAdmin();
        
        // Create manager users
        $managers = $this->createManagers();
        
        // Create receptionist users
        $receptionists = $this->createReceptionists();
        
        // Create floors and rooms
        $floors = $this->createFloors($managers);
        $rooms = $this->createRooms($floors);
        
        // Create pending clients (not approved)
        $pendingClients = $this->createPendingClients();
        
        // Create approved clients with managers
        $approvedClients = $this->createApprovedClients($receptionists);
        
        // Create reservations for pending clients
        $pendingReservations = $this->createPendingReservations($pendingClients, $rooms);
        
        // Create reservations for approved clients
        $approvedReservations = $this->createApprovedReservations($approvedClients, $rooms, $receptionists);
        
        // Create payments
        $this->createPayments($approvedReservations);
        
        // Output summary
        $this->command->info('Client Approval Test Seeder completed successfully!');
        $this->command->info('Created:');
        $this->command->info('- 1 Admin user');
        $this->command->info('- ' . count($managers) . ' Manager users');
        $this->command->info('- ' . count($receptionists) . ' Receptionist users');
        $this->command->info('- ' . count($floors) . ' Floors');
        $this->command->info('- ' . count($rooms) . ' Rooms');
        $this->command->info('- ' . count($pendingClients) . ' Pending clients');
        $this->command->info('- ' . count($approvedClients) . ' Approved clients');
        $this->command->info('- ' . count($pendingReservations) . ' Pending reservations');
        $this->command->info('- ' . count($approvedReservations) . ' Approved reservations');
    }
    
    /**
     * Create admin user
     */
    private function createAdmin()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'national_id' => '1000000000001',
            'mobile' => '1000000001',
            'country' => 'Admin Country',
            'gender' => 'male',
            'role' => 'admin',
            'is_approved' => 1,
        ]);
        
        $admin->assignRole('admin');
        
        return $admin;
    }
    
    /**
     * Create manager users
     */
    private function createManagers($count = 2)
    {
        $managers = [];
        
        for ($i = 1; $i <= $count; $i++) {
            $manager = User::create([
                'name' => "Manager User {$i}",
                'email' => "manager{$i}@example.com",
                'password' => Hash::make('password'),
                'national_id' => "2000000000{$i}",
                'mobile' => "2000000{$i}",
                'country' => 'Manager Country',
                'gender' => $i % 2 == 0 ? 'female' : 'male',
                'role' => 'manager',
                'is_approved' => 1,
            ]);
            
            $manager->assignRole('manager');
            $managers[] = $manager;
        }
        
        return $managers;
    }
    
    /**
     * Create receptionist users
     */
    private function createReceptionists($count = 3)
    {
        $receptionists = [];
        
        for ($i = 1; $i <= $count; $i++) {
            $receptionist = User::create([
                'name' => "Receptionist User {$i}",
                'email' => "receptionist{$i}@example.com",
                'password' => Hash::make('password'),
                'national_id' => "3000000000{$i}",
                'mobile' => "3000000{$i}",
                'country' => 'Receptionist Country',
                'gender' => $i % 2 == 0 ? 'female' : 'male',
                'role' => 'receptionist',
                'is_approved' => 1,
            ]);
            
            $receptionist->assignRole('receptionist');
            $receptionists[] = $receptionist;
        }
        
        return $receptionists;
    }
    
    /**
     * Create floors
     */
    private function createFloors($managers, $count = 3)
    {
        $floors = [];
        
        for ($i = 1; $i <= $count; $i++) {
            $floor = Floor::create([
                'name' => "Floor {$i}",
                'number' => $i,
                'manager_id' => $managers[($i - 1) % count($managers)]->id,
            ]);
            
            $floors[] = $floor;
        }
        
        return $floors;
    }
    
    /**
     * Create rooms
     */
    private function createRooms($floors, $roomsPerFloor = 5)
    {
        $rooms = [];
        $roomNumber = 100;
        
        foreach ($floors as $floor) {
            for ($i = 1; $i <= $roomsPerFloor; $i++) {
                $roomNumber++;
                $room = Room::create([
                    'room_number' => $roomNumber,
                    'room_capacity' => rand(1, 4),
                    'price' => rand(50, 300) * 100, // Store in cents
                    'status' => 'available',
                    'manager_id' => $floor->manager_id,
                    'floor_id' => $floor->id,
                ]);
                
                $rooms[] = $room;
            }
        }
        
        return $rooms;
    }
    
    /**
     * Create pending clients (not approved)
     */
    private function createPendingClients($count = 5)
    {
        $pendingClients = [];
        
        for ($i = 1; $i <= $count; $i++) {
            $client = User::create([
                'name' => "Pending Client {$i}",
                'email' => "pending.client{$i}@example.com",
                'password' => Hash::make('password'),
                'national_id' => "4000000000{$i}",
                'mobile' => "4000000{$i}",
                'country' => 'Client Country',
                'gender' => $i % 2 == 0 ? 'female' : 'male',
                'role' => 'client',
                'is_approved' => 0, // Not approved
                'manager_id' => null,
            ]);
            
            $client->assignRole('client');
            $pendingClients[] = $client;
        }
        
        return $pendingClients;
    }
    
    /**
     * Create approved clients with managers
     */
    private function createApprovedClients($receptionists, $count = 5)
    {
        $approvedClients = [];
        
        for ($i = 1; $i <= $count; $i++) {
            $client = User::create([
                'name' => "Approved Client {$i}",
                'email' => "approved.client{$i}@example.com",
                'password' => Hash::make('password'),
                'national_id' => "5000000000{$i}",
                'mobile' => "5000000{$i}",
                'country' => 'Client Country',
                'gender' => $i % 2 == 0 ? 'female' : 'male',
                'role' => 'client',
                'is_approved' => 1, // Approved
                'manager_id' => $receptionists[($i - 1) % count($receptionists)]->id,
            ]);
            
            $client->assignRole('client');
            $approvedClients[] = $client;
        }
        
        return $approvedClients;
    }
    
    /**
     * Create pending reservations for pending clients
     */
    private function createPendingReservations($pendingClients, $rooms, $count = 8)
    {
        $pendingReservations = [];
        $today = Carbon::today();
        
        for ($i = 1; $i <= $count; $i++) {
            $client = $pendingClients[($i - 1) % count($pendingClients)];
            $room = $rooms[array_rand($rooms)];
            $checkInDate = $today->copy()->addDays(rand(5, 30));
            $checkOutDate = $checkInDate->copy()->addDays(rand(1, 7));
            
            $reservation = Reservation::create([
                'client_id' => $client->id,
                'room_id' => $room->id,
                'accompany_number' => rand(1, $room->room_capacity),
                'price_paid' => $room->price * rand(1, 3), // Some multiplier for longer stays
                'receptionist_id' => null, // No receptionist assigned yet
                'check_in_date' => $checkInDate->format('Y-m-d'),
                'check_out_date' => $checkOutDate->format('Y-m-d'),
                'status' => 'pending',
            ]);
            
            $pendingReservations[] = $reservation;
        }
        
        return $pendingReservations;
    }
    
    /**
     * Create approved reservations for approved clients
     */
    private function createApprovedReservations($approvedClients, $rooms, $receptionists, $count = 10)
    {
        $approvedReservations = [];
        $today = Carbon::today();
        $statuses = ['confirmed', 'checked_in', 'checked_out'];
        
        for ($i = 1; $i <= $count; $i++) {
            $client = $approvedClients[($i - 1) % count($approvedClients)];
            $room = $rooms[array_rand($rooms)];
            $receptionist = $receptionists[array_rand($receptionists)];
            $status = $statuses[array_rand($statuses)];
            
            // Different date logic based on status
            if ($status == 'confirmed') {
                $checkInDate = $today->copy()->addDays(rand(5, 30));
                $checkOutDate = $checkInDate->copy()->addDays(rand(1, 7));
            } elseif ($status == 'checked_in') {
                $checkInDate = $today->copy()->subDays(rand(1, 3));
                $checkOutDate = $today->copy()->addDays(rand(1, 7));
            } else { // checked_out
                $checkInDate = $today->copy()->subDays(rand(10, 20));
                $checkOutDate = $today->copy()->subDays(rand(1, 9));
            }
            
            $reservation = Reservation::create([
                'client_id' => $client->id,
                'room_id' => $room->id,
                'accompany_number' => rand(1, $room->room_capacity),
                'price_paid' => $room->price * rand(1, 3), // Some multiplier for longer stays
                'receptionist_id' => $receptionist->id,
                'check_in_date' => $checkInDate->format('Y-m-d'),
                'check_out_date' => $checkOutDate->format('Y-m-d'),
                'status' => $status,
            ]);
            
            $approvedReservations[] = $reservation;
            
            // Update room status if needed
            if ($status == 'checked_in') {
                $room->update(['status' => 'occupied']);
            }
        }
        
        return $approvedReservations;
    }
    
    /**
     * Create payments for approved reservations
     */
    private function createPayments($approvedReservations)
    {
        foreach ($approvedReservations as $reservation) {
            Payment::create([
                'reservation_id' => $reservation->id,
                'stripe_payment_id' => 'cs_test_' . md5($reservation->id . time() . rand(1000, 9999)),
                'amount' => $reservation->price_paid,
                'status' => 'paid',
            ]);
        }
    }
}