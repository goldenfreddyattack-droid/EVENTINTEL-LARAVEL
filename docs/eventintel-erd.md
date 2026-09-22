# EventIntel Entity Relationship Diagram

This ERD reflects the business and legacy tables that exist in the current MySQL database. Laravel framework tables and Firebase paths are documented separately. The diagram shows application relationships inferred from matching ID columns; the current schema does not define database foreign-key constraints for these relationships.

## ERD

```mermaid
erDiagram
    USERS {
        int user_id PK
        string username UK
        string full_name
        string email
        string password
        string role
        date email_verified_at
        string status
        string first_name
        string last_name
        string middle_initial
        int age
        string gender
        string phone
        string province
        string municipality
        string barangay
        string postal_code
        string business_name
        text business_address
        string valid_id
        string business_permit
        string face_capture
        timestamp created_at
    }

    EVENTS {
        int event_id PK
        int user_id
        string title
        string event_type
        string theme
        decimal budget
        date event_date
        time event_time
        time event_end_time
        int guest_count
        string venue_name
        string venue_status
        text venue_address
        decimal latitude
        decimal longitude
        string clothes_status
        string clothes
        string catering_status
        string catering
        string host_status
        string host
        string soundsnlights_status
        string soundsnlights
        string photographer_status
        string photographer
        string coordinator
        string coordinator_package
        string coordinator_status
        text coordinator_proposal
        string payment_method
        string status
        string payment_status
        text clothes_note
        text venue_note
        text catering_note
        text host_note
        text s_l_note
        text photographer_note
        timestamp created_at
    }

    SUPPLIER_SERVICES {
        int service_id PK
        int user_id
        string category
        string style
        string name
        text description
        decimal price
        int capacity
        string venue_add_ons
        decimal venueaddons_price1
        decimal venueaddons_price2
        decimal venueaddons_price3
        decimal venueaddons_price4
        decimal venueaddons_price5
        text venueaddons_details1
        text venueaddons_details2
        text venueaddons_details3
        text venueaddons_details4
        text venueaddons_details5
        blob service_pic
        blob service_pic1
        blob service_pic2
        blob service_pic3
        blob service_pic4
        blob service_pic5
        text address
        decimal latitude
        decimal longitude
        decimal rating
        timestamp created_at
    }

    INVITATIONS {
        int invitation_id PK
        int event_id
        string title
        text message
        string theme_color
        string font_style
        string button_text
        string background_image
        string template
        timestamp created_at
    }

    GUESTS {
        int guest_id PK
        int event_id
        string name
        string email
        string phone
        string qr_code
        string rsvp_status
        boolean attended
        datetime scanned_at
        timestamp created_at
    }

    CUSTOM_EVENT_REQUESTS {
        bigint request_id PK
        bigint event_id
        int client_id
        int coordinator_id
        string event_type
        date event_date
        string venue_preference
        int guest_count
        string theme
        decimal budget
        text required_services
        text special_requests
        text additional_notes
        string status
        timestamp created_at
        timestamp updated_at
    }

    BOOKINGS {
        int booking_id PK
        int event_id
        int service_id
        string status
        timestamp created_at
    }

    COORDINATOR_PACKAGES {
        int package_id PK
        int coordinator_id
        string name
        decimal price
        text description
        text inclusions
        boolean is_featured
        timestamp created_at
    }

    COORDINATOR_REVIEWS {
        int review_id PK
        int coordinator_id
        int user_id
        int event_id
        int rating
        text comment
        timestamp created_at
    }

    EVENT_SUPPLIER_REVIEWS {
        int id PK
        int event_id
        string review_token
        string service_column
        string supplier_name
        int rating
        text review_text
        string reviewer_name
        timestamp created_at
        timestamp updated_at
    }

    EVENT_REVIEW_LINKS {
        bigint id PK
        bigint event_id UK
        string token
        timestamp created_at
        timestamp updated_at
    }

    EVENT_REVIEW_FOLDER_TOKENS {
        bigint id PK
        bigint event_id UK
        string token UK
        timestamp created_at
        timestamp updated_at
    }

    STUDENT_3A_TBL {
        bigint id PK
        string fname
        string lname
        string mname
        string add
        date dob
        timestamp created_at
        timestamp updated_at
    }

    PAYMENTS {
        bigint payment_id PK
        bigint event_id
        bigint user_id
        string reference_no
        decimal amount
        string status
        timestamp verified_at
        timestamp created_at
    }


    USERS ||--o{ EVENTS : creates
    USERS ||--o{ SUPPLIER_SERVICES : owns
    USERS ||--o{ CUSTOM_EVENT_REQUESTS : submits
    USERS ||--o{ COORDINATOR_PACKAGES : coordinates
    USERS ||--o{ COORDINATOR_REVIEWS : receives
    USERS ||--o{ COORDINATOR_REVIEWS : writes
    USERS ||--o{ PAYMENTS : makes

    EVENTS ||--o| INVITATIONS : has
    EVENTS ||--o{ GUESTS : contains
    EVENTS ||--o{ CUSTOM_EVENT_REQUESTS : receives
    EVENTS ||--o{ BOOKINGS : includes
    EVENTS ||--o{ COORDINATOR_REVIEWS : receives
    EVENTS ||--o{ EVENT_SUPPLIER_REVIEWS : receives
    EVENTS ||--o| EVENT_REVIEW_LINKS : exposes
    EVENTS ||--o| EVENT_REVIEW_FOLDER_TOKENS : owns
    EVENTS ||--o{ PAYMENTS : has

    SUPPLIER_SERVICES ||--o{ BOOKINGS : booked
```

`COORDINATOR_PROPOSALS` is intentionally not shown because there is no such table in the current database. Proposal information is stored in `events.coordinator_proposal`, while request information is stored in `custom_event_requests`.

`EVENT_SUPPLIER_REVIEWS` is not directly connected to `SUPPLIER_SERVICES` because it stores the supplier name and selected event service column as text rather than a `service_id`. The Mermaid field `s_l_note` represents the physical MySQL column named `s&l_note`.

## Firebase Realtime Database

Firebase is used as a separate document tree for messaging and the newsfeed. These paths are not MySQL tables and are shown separately from the relational ERD.

```mermaid
flowchart TD
    MESSAGES["messages/{threadKey}/{eventId}/{messageId}"]
    MESSAGE_FIELDS["message_id, sender_id, sender_name, receiver_id, event_id, message, timestamp, read_by"]
    POSTS["newsfeed/posts/{postId}"]
    POST_FIELDS["user_id, content, image_path, created_at"]
    COMMENTS["newsfeed/comments/{commentId}"]
    COMMENT_FIELDS["post_id, user_id, comment, created_at"]
    LIKES["newsfeed/likes/{postId}/{userId}"]
    LIKE_VALUE["boolean: liked"]

    MESSAGES --> MESSAGE_FIELDS
    POSTS --> POST_FIELDS
    COMMENTS --> COMMENT_FIELDS
    LIKES --> LIKE_VALUE
    POSTS -->|has| COMMENTS
    POSTS -->|has| LIKES
```

## Included Tables

- `users`: authentication, roles, profiles, clients, suppliers, coordinators, and administrators.
- `events`: central event record and selected service/coordinator/payment status.
- `supplier_services`: supplier catalog, capacity, pricing, availability, and ratings.
- `invitations` and `guests`: invitation and RSVP management.
- `custom_event_requests`: coordinator booking requests.
- `bookings`: event-to-supplier-service booking links.
- `coordinator_packages` and `coordinator_reviews`: coordinator packages and feedback.
- `event_supplier_reviews`, `event_review_links`, and `event_review_folder_tokens`: event-specific review workflow and review sessions.
- `payments`: referenced by the GCash payment webhook and payment status flow.
- `3a_tbl`: legacy student-management data.

## Excluded Tables

These tables exist in the database but are not shown in the business ERD because they support Laravel infrastructure:

- `cache`, `cache_locks`, `jobs`, `job_batches`, and `failed_jobs`
- `migrations`, `password_reset_tokens`, and `sessions`

## Schema Note

`payments` is created by the `2026_09_20_000000_create_payments_table` migration. It stores online checkout records and webhook verification status. Cash payments remain represented by `events.payment_method` and `events.payment_status`.

`bookings`, `invitations`, and `guests` exist in the database dump but do not have matching current Laravel creation migrations in this repository. `coordinator_packages`, `coordinator_reviews`, and `3a_tbl` also exist in the database dump and are retained here even though they are legacy or conditional application areas.

`event_supplier_reviews.review_token` separates reviews submitted through different QR/link sessions. `event_review_links` receives a new token whenever QR & Link is generated, while `event_review_folder_tokens` creates one reusable token per event for the authenticated Review Folder.

ID columns are shown as relationships based on application usage and matching names. The database currently uses indexes and unique keys but does not enforce these business relationships with foreign-key constraints.

Firebase Realtime Database uses the paths shown above. Message threads are keyed by event and participant IDs; newsfeed posts, comments, and likes are stored under their respective Firebase branches.
