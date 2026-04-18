# Class Diagram - Journey 2 (Booking to E-Ticket)

Dokumen ini merinci struktur kelas untuk implementasi US 2.2 hingga 2.5, yang merupakan kelanjutan dari fitur pencarian (US 2.1).

## Mermaid Diagram

```mermaid
classDiagram
    %% --- LAYER CONTROLLER ---
    class FlightSearchController {
        -FlightSearchService $service
        +show() View
        +search(FlightSearchRequest $request) Response
    }

    class BookingController {
        -BookingService $service
        +showPassengerForm(scheduleId) View
        +storeBooking(BookingRequest $request) Response
        +checkout(bookingId) View
        +confirmPayment(bookingId) Response
    }

    %% --- LAYER SERVICE & REPOSITORY ---
    class FlightSearchService {
        -FlightRepository $repository
        +search(criteria) array
    }

    class BookingService {
        -BookingRepository $repository
        +createBooking(data)
        +validatePaymentTimer(id)
        +issueTicket(id)
    }

    class FlightRepository {
        +search(origin, destination, date, class) Collection
    }

    class BookingRepository {
        +save(Booking $booking)
        +findWithDetails(id)
    }

    %% --- LAYER MODELS (DATABASE) ---
    class FlightSchedule {
        -int id
        -string flight_number
        -date departure_date
        +airline()
        +seatClasses()
    }

    class Booking {
        -int id
        -string booking_code
        -string status
        -datetime payment_expired_at
    }

    class Passenger {
        -int id
        -string nik
        -string full_name
    }

    %% --- RELATIONSHIPS ---
    FlightSearchController --> FlightSearchService : uses
    FlightSearchService --> FlightRepository : uses

    %% Alur Utama: Dari Hasil Pencarian ke Form Booking
    FlightSearchController ..> BookingController : Redirect (User memilih tiket)

    BookingController --> BookingService : uses
    BookingService --> BookingRepository : uses

    BookingRepository --> Booking : manages
    Booking "1" -- "*" Passenger : contains
    Booking "*" -- "1" FlightSchedule : related to
```
