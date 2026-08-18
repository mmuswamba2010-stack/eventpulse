# 📑 EVENT PULSE — ARCHITECTURE & SPÉCIFICATIONS TECHNIQUES

## 1. VUE D'ENSEMBLE DU SITE WEB
Event Pulse est un site web de billetterie en ligne et de gestion d'événements. 
L'application permet :
- Aux organisateurs de créer des événements, de suivre les ventes et de contrôler les accès via scan QR Code.
- Aux participants de parcourir les événements, d'acheter des billets et d'accéder à leurs tickets avec QR Code (et export PDF).

---

## 2. RÔLES ET AUTHENTIFICATION
Utilisation de Laravel Breeze avec deux rôles distincts (champ `role` sur la table `users`) :
- `organizer` : Accès au tableau de bord, création/édition d'événements, scanner de QR Code.
- `participant` : Consultation du catalogue, achat/réservation de billets, consultation de la page "Mes Billets".

---

## 3. STRUCTURE DE LA BASE DE DONNÉES (SCHEMA)

### Table `users`
- `id` (BigIncrements, PK)
- `name` (String)
- `email` (String, Unique)
- `password` (String)
- `role` (Enum: 'organizer', 'participant', Default: 'participant')
- `phone` (String, Nullable)
- `timestamps`

### Table `events`
- `id` (BigIncrements, PK)
- `user_id` (ForeignKey -> users.id, CascadeOnDelete)
- `title` (String)
- `slug` (String, Unique)
- `description` (Text)
- `location` (String)
- `event_date` (DateTime)
- `capacity` (Integer)
- `price` (Decimal 8,2) — prix minimum dérivé des `ticket_types` (affichage catalogue)
- `image_path` (String, Nullable)
- `status` (Enum: 'draft', 'published', 'cancelled')
- `placement_mode` (String: 'standing' | 'seated', Default: 'standing')
- `is_paid` (Boolean, Default: false) — frais de publication réglés
- `publication_fee` (Decimal 8,2, Default: 20.00 $)
- `payment_method` (String, Nullable) — mobile_money | card
- `paid_at` (Timestamp, Nullable)
- `timestamps`

### Table `ticket_types`
- `id` (BigIncrements, PK)
- `event_id` (ForeignKey -> events.id, CascadeOnDelete)
- `name` (String) — ex. Standard, VIP, VVIP (saisi par l'organisateur)
- `price` (Decimal 8,2) — **tarif saisi par l'organisateur**
- `quantity` (Integer) — stock de ce pass
- `is_seated` (Boolean) — true si l'événement est en mode `seated`
- `timestamps`

### Table `tickets`
- `id` (BigIncrements, PK)
- `event_id` (ForeignKey -> events.id, CascadeOnDelete)
- `ticket_type_id` (ForeignKey -> ticket_types.id, Nullable, NullOnDelete)
- `user_id` (ForeignKey -> users.id, CascadeOnDelete)
- `ticket_code` (String, Unique) — code technique opaque pour le QR Code / scan
- `ticket_number` (String, Unique) — référence humaine courte (ex. EP-98A4-K7MX), affichée en UI/PDF
- `seat_number` (String, Nullable) — ex. "Rangée B / Siège 14" si place assise
- `payment_method` (String, Nullable) — mobile_money | card | cash
- `mobile_provider` (String, Nullable) — mpesa | orange_money | airtel_money (si Mobile Money)
- `status` (Enum: 'valid', 'used', 'cancelled', Default: 'valid')
- `scanned_at` (DateTime, Nullable)
- `timestamps`

### Table `scans`
- `id` (BigIncrements, PK)
- `ticket_id` (ForeignKey -> tickets.id, CascadeOnDelete)
- `scanned_by` (ForeignKey -> users.id)
- `status` (Enum: 'success', 'already_used', 'invalid')
- `timestamps`

---

## 4. RELATIONS ELOQUENT
- **User** : `hasMany(Event)`, `hasMany(Ticket)`
- **Event** : `belongsTo(User)`, `hasMany(Ticket)`, `hasMany(TicketType)`
- **TicketType** : `belongsTo(Event)`, `hasMany(Ticket)`
- **Ticket** : `belongsTo(Event)`, `belongsTo(TicketType)`, `belongsTo(User)`, `hasMany(Scan)`
- **Scan** : `belongsTo(Ticket)`, `belongsTo(User, 'scanned_by')`

---

## 4bis. PLACEMENTS & TYPES DE BILLETS
- À la création, l'organisateur choisit `placement_mode` : **standing** (debout / libre) ou **seated** (places numérotées).
- Il définit un ou plusieurs `ticket_types` (nom + **prix libre** + quantité).
- Sur chaque fiche événement, les modes de paiement participants sont toujours affichés (Mobile Money, Carte, Espèces) — **c'est le client qui choisit** à la réservation.
- À l'achat : le participant choisit un type **et** son mode de paiement ; si mode assis, `tickets.seat_number` est attribué automatiquement.
- Affichage billet :
  - Debout → `ACCÈS DEBOUT / PLACEMENT LIBRE (ZONE …)`
  - Assis → `ZONE VIP — Rangée B / Siège 14`

---

## 5. PLAN DES ROUTES (routes/web.php)

### Public / Invite :
- `GET /` -> EventController@index (Liste des événements)
- `GET /events/{slug}` -> EventController@show (Détail de l'événement)

### Authentifié (Participant) :
- `POST /events/{id}/book` -> TicketController@store (Réserver/Acheter)
- `GET /my-tickets` -> TicketController@index (Liste de mes billets)
- `GET /my-tickets/{id}` -> TicketController@show (Afficher le ticket + QR Code)
- `GET /my-tickets/{id}/download` -> TicketController@downloadPdf (Télécharger le PDF)

### Authentifié (Organisateur - Middleware Role:organizer) :
- `GET /organizer/dashboard` -> Organizer\DashboardController@index
- `GET /organizer/events` -> Organizer\EventController@index
- `GET /organizer/events/create` -> Organizer\EventController@create
- `POST /organizer/events` -> Organizer\EventController@store
- `GET /organizer/events/{id}/edit` -> Organizer\EventController@edit
- `PUT /organizer/events/{id}` -> Organizer\EventController@update
- `DELETE /organizer/events/{id}` -> Organizer\EventController@destroy
- `GET /organizer/events/{id}/pay` -> Organizer\EventController@pay (Récapitulatif + choix du moyen de paiement)
- `POST /organizer/events/{id}/pay` -> Organizer\EventController@processPayment (Valider le paiement, publie l'événement)
- `GET /organizer/scan` -> Organizer\ScanController@index (Scanner caméra)
- `POST /organizer/scan/validate` -> Organizer\ScanController@validateTicket (API Validation AJAX)

---

## 6bis. FRAIS DE PUBLICATION DES ÉVÉNEMENTS
- À la création, un événement est toujours enregistré `status = draft` / `is_paid = false` : il n'apparaît jamais dans le catalogue public tant qu'il n'est pas payé.
- L'organisateur est redirigé vers `/organizer/events/{id}/pay` pour régler les frais fixes (`Event::PUBLICATION_FEE`, 20 $ par défaut).
- Deux moyens de paiement simulés : Mobile Money (M-Pesa / Orange Money / Airtel Money + numéro de téléphone) ou Carte Bancaire (Visa/Mastercard).
- Une fois le paiement validé (simulation, pas d'intégration API réelle), `is_paid = true` et `status = published` : l'événement devient visible dans le catalogue public (`EventController@index`/`show` filtrent sur `status = published` ET `is_paid = true`).
- Un événement déjà payé ne peut plus être repassé manuellement en `draft` ; un événement non payé ne peut pas être mis en `published` sans passer par le paiement.

## 6. MODULES TECHNIQUES DÉSIRÉS
- **Génération QR Code** : via `simplesoftwareio/simple-qrcode`
- **Lecture QR Code** : via la caméra web avec le package JS `html5-qrcode`
- **Export PDF** : via `barryvdh/laravel-dompdf`
## 7. RÈGLES DE DESIGN & UI
- **Icônes vs Emojis** : Ne JAMAIS utiliser d'emojis dans l'interface utilisateur. Utiliser exclusivement des icônes SVG (Heroicons / Lucide) stylisées avec Tailwind CSS.
- **Style** : Design moderne, propre et épuré avec Tailwind CSS dans toute le site.