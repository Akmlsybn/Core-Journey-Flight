# Flight Search Engine — Class Diagram (Eloquent)

Diagram ini menggambarkan model domain penerbangan beserta atribut `$fillable`, cast numerik/tanggal yang didefinisikan di model, method relasi Eloquent, dan kardinalitas antar entitas.

```mermaid
classDiagram
    %% Models
    class Airline {
        -int id
        -string airline_name
        -string airline_code
        -string logo_url
        -timestamp created_at
        -timestamp updated_at
        
        +flightSchedules() FlightSchedule[]
    }
    
    class FlightSchedule {
        -int id
        -int airline_id
        -int route_id
        -string flight_number
        -string origin
        -string destination
        -date departure_date
        -datetime departure_time
        -datetime arrival_time
        -decimal base_price
        -string flight_status
        -timestamp created_at
        -timestamp updated_at
        
        +airline() Airline
        +route() Route
        +seatClasses() FlightSeatClass[]
    }
    
    class Airport {
        -int id
        -string airport_code
        -string airport_name
        -string city_name
        -string country_name
        -timestamp created_at
        -timestamp updated_at
        
        +originRoutes() Route[]
        +destinationRoutes() Route[]
    }
    
    class Route {
        -int id
        -int origin_id
        -int destination_id
        -string route_code
        -decimal distance_km
        -timestamp created_at
        -timestamp updated_at
        
        +originAirport() Airport
        +destinationAirport() Airport
        +flightSchedules() FlightSchedule[]
    }
    
    class FlightSeatClass {
        -int id
        -int flight_schedule_id
        -string seat_class
        -int seat_capacity
        -int available_seats
        -decimal class_price
        -timestamp created_at
        -timestamp updated_at
        
        +flightSchedule() FlightSchedule
    }
    
    %% Request
    class FlightSearchRequest {
        -string origin
        -string destination
        -date departure_date
        -int passenger_count
        -string seat_class
        
        +authorize() bool
        +rules() array
        +messages() array
        +validated() array
    }
    
    %% Repository
    class FlightRepository {
        -FlightSchedule $flightSchedule
        
        +search(origin, destination, date, class) Collection
        -calculateDuration(flightSchedule) int
        -getPrice(seatClass) decimal
        -isAirportValid(code) bool
        -checkAvailability(seatClass, seats) bool
    }
    
    %% Service
    class FlightSearchService {
        -FlightRepository $repository
        
        +search(criteria) array
        -validateCriteria(criteria) bool
        -formatFlightData(flightSchedules) array
        -handleError(exception) array
    }
    
    %% Controller
    class FlightSearchController {
        -FlightSearchService $service
        
        +show() View
        +search(request) Response
    }
    
    %% Relationships
    FlightSearchController --> FlightSearchRequest : uses
    FlightSearchController --> FlightSearchService : uses
    FlightSearchService --> FlightRepository : uses
    FlightRepository --> FlightSchedule : queries
    FlightSchedule --> Airline : belongsTo
    FlightSchedule --> Route : belongsTo
    FlightSchedule --> FlightSeatClass : hasMany
    Route --> Airport : belongsTo (origin)
    Route --> Airport : belongsTo (destination)
    Airport --> Route : hasMany (origin)
    Airport --> Route : hasMany (destination)
    FlightSeatClass --> FlightSchedule : belongsTo
    
    %% Notes
    note "Validasi Rules:
    - passenger_count: 1-7
    - departure_date: >= hari ini
    - origin != destination"
        FlightSearchRequest
```

## Catatan implementasi

- Kolom `timestamps()` (`created_at`, `updated_at`) ada di setiap tabel migrasi; tidak digambar di kotak class agar diagram tetap fokus pada domain.
- Atribut `origin` dan `destination` pada `FlightSchedule` adalah denormalisasi kode bandara (string); relasi kanonik trayek tetap melalui `route_id` → `Route` → `Airport`.
