# Flight Search Engine — Class Diagram (Eloquent)

Diagram ini menggambarkan model domain penerbangan beserta atribut `$fillable`, cast numerik/tanggal yang didefinisikan di model, method relasi Eloquent, dan kardinalitas antar entitas.

```mermaid
classDiagram
    direction TB

    class Airport {
        +int id
        +string airport_code
        +string airport_name
        +string city_name
        +string country_name
        +outgoingRoutes() HasMany Route
        +incomingRoutes() HasMany Route
    }

    class Airline {
        +int id
        +string airline_code
        +string airline_name
        +flightSchedules() HasMany FlightSchedule
    }

    class Route {
        +int id
        +int origin_id FK
        +int destination_id FK
        +string route_code
        +int distance_km
        +originAirport() BelongsTo Airport
        +destinationAirport() BelongsTo Airport
        +flightSchedules() HasMany FlightSchedule
    }

    class FlightSchedule {
        +int id
        +int airline_id FK
        +int route_id FK
        +string flight_number
        +string origin
        +string destination
        +date departure_date
        +time departure_time
        +time arrival_time
        +decimal base_price
        +string flight_status
        +airline() BelongsTo Airline
        +route() BelongsTo Route
        +seatClasses() HasMany FlightSeatClass
    }

    class FlightSeatClass {
        +int id
        +int flight_schedule_id FK
        +string seat_class
        +int seat_capacity
        +int available_seats
        +decimal class_price
        +flightSchedule() BelongsTo FlightSchedule
    }

    Airport "1" --> "*" Route : origin_id
    Airport "1" --> "*" Route : destination_id
    Airline "1" --> "*" FlightSchedule
    Route "1" --> "*" FlightSchedule
    FlightSchedule "1" --> "*" FlightSeatClass
```

## Catatan implementasi

- Kolom `timestamps()` (`created_at`, `updated_at`) ada di setiap tabel migrasi; tidak digambar di kotak class agar diagram tetap fokus pada domain.
- Atribut `origin` dan `destination` pada `FlightSchedule` adalah denormalisasi kode bandara (string); relasi kanonik trayek tetap melalui `route_id` → `Route` → `Airport`.
