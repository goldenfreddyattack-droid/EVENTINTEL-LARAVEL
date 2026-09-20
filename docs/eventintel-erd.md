# EventIntel Entity Relationship Diagram

This ERD includes only tables referenced by active Laravel controllers and routes. Legacy standalone PHP pages, framework tables, and conditional tables that are not part of the current active schema are excluded.

## ERD

```mermaid
erDiagram
    USERS {
        bigint user_id PK
        string full_name
        string email
        string role
        string status
    }

    EVENTS {
        bigint event_id PK
        int user_id FK
        string title
        string event_type
        string theme
        decimal budget
        date event_date
        time event_time
        time event_end_time
        int guest_count
        string venue_name
        string clothes
        string catering
        string host
        string soundsnlights
        string photographer
        string coordinator
        string coordinator_status
        string status
        string payment_status
        timestamp created_at
    }

    SUPPLIER_SERVICES {
        bigint service_id PK
        int user_id FK
        string category
        string name
        text description
        decimal price
        int capacity
        text address
        decimal rating
        timestamp created_at
    }

    INVITATIONS {
        bigint invitation_id PK
        bigint event_id FK
        string title
        text message
        string theme_color
        string font_style
        string button_text
        string background_image
        timestamp created_at
    }

    GUESTS {
        bigint guest_id PK
        bigint event_id FK
        string name
        string email
        string phone
        string invitation_status
        string qr_code
    }

    CUSTOM_EVENT_REQUESTS {
        bigint request_id PK
        bigint event_id FK
        int client_id FK
        int coordinator_id FK
        string event_type
        date event_date
        string venue_preference
        int guest_count
        string theme
        decimal budget
        text required_services
        string status
    }

    COORDINATOR_PROPOSALS {
        bigint proposal_id PK
        bigint event_id FK
        int coordinator_id FK
        decimal amount
        string status
        text proposal
        timestamp created_at
    }

    EVENT_SUPPLIER_REVIEWS {
        bigint id PK
        bigint event_id FK
        string review_token
        string service_column
        string supplier_name
        int rating
        text review_text
        string reviewer_name
        timestamp created_at
    }

    EVENT_REVIEW_LINKS {
        bigint id PK
        bigint event_id FK
        string token
        timestamp created_at
    }

    EVENT_REVIEW_FOLDER_TOKENS {
        bigint id PK
        bigint event_id FK
        string token UK
        timestamp created_at
    }

    USER_SERVICE_BOOKMARKS {
        bigint bookmark_id PK
        int user_id FK
        bigint service_id FK
        timestamp created_at
    }

    PAYMENTS {
        bigint payment_id PK
        bigint event_id FK
        int user_id FK
        string reference_no
        decimal amount
        string status
        timestamp created_at
    }


    USERS ||--o{ EVENTS : creates
    USERS ||--o{ SUPPLIER_SERVICES : owns
    USERS ||--o{ CUSTOM_EVENT_REQUESTS : submits
    USERS ||--o{ COORDINATOR_PROPOSALS : coordinates
    USERS ||--o{ USER_SERVICE_BOOKMARKS : saves
    USERS ||--o{ PAYMENTS : makes

    EVENTS ||--o| INVITATIONS : has
    EVENTS ||--o{ GUESTS : contains
    EVENTS ||--o{ CUSTOM_EVENT_REQUESTS : receives
    EVENTS ||--o{ COORDINATOR_PROPOSALS : receives
    EVENTS ||--o{ EVENT_SUPPLIER_REVIEWS : receives
    EVENTS ||--o| EVENT_REVIEW_LINKS : exposes
    EVENTS ||--o| EVENT_REVIEW_FOLDER_TOKENS : owns
    EVENTS ||--o{ PAYMENTS : has

    SUPPLIER_SERVICES ||--o{ USER_SERVICE_BOOKMARKS : bookmarked
```

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
- `custom_event_requests` and `coordinator_proposals`: coordinator booking workflow.
- `event_supplier_reviews`, `event_review_links`, and `event_review_folder_tokens`: event-specific review workflow and review sessions.
- `user_service_bookmarks`: saved supplier services.
- `payments`: referenced by the GCash payment webhook and payment status flow.

## Excluded Tables

These are excluded because they are framework-only, legacy, unused by active Laravel event features, or conditionally referenced without a current schema:

- `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `password_reset_tokens`, and `sessions`
- `3A_tbl`, the legacy student-management table
- `event_services`, which is checked conditionally but is not created in the current database
- `coordinator_profile`, `coordinator_gallery`, `coordinator_packages`, and `coordinator_reviews`, which are accessed conditionally but are not present in the current migration set
- Legacy PHP pages under `resources/views/userui/php/`

## Schema Note

`payments`, `guests`, and `coordinator_proposals` are directly referenced by active controllers, but their table-creation migrations are not included in the current migration directory. Their displayed columns are therefore based on the fields used by those controllers. The ERD shows application relationships, not database-enforced foreign keys.

`event_supplier_reviews.review_token` separates reviews submitted through different QR/link sessions. `event_review_links` receives a new token whenever QR & Link is generated, while `event_review_folder_tokens` creates one reusable token per event for the authenticated Review Folder.

Firebase Realtime Database uses the paths shown above. Message threads are keyed by event and participant IDs; newsfeed posts, comments, and likes are stored under their respective Firebase branches.
