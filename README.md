# Missing People Reporter - Comprehensive Documentation

The **Missing People Reporter** plugin is a mission-critical WordPress solution for [MissingPeople.co.ke](https://missingpeople.co.ke), enabling structured reporting and tracking of missing persons in Kenya.

---

## 🚀 1. Key Features

### 📅 Case Management & Risk Levels
- **Dynamic Status**: Track cases as `Missing`, `Found - Safe`, `Found - Deceased`, or `Cold Case`.
- **Risk Assessment**: Categorize cases as `Low`, `Medium`, or `High` risk.
- **Visual Urgency**: "High Risk" cases feature an automated pulsing red badge for maximum visibility.

### 🗺️ Dynamic Map Integration
- **Interactive Location Pinning**: Admins can pin the exact "Last Seen" coordinates on an OpenStreetMap interface.
- **Auto-Geocoding**: A "Find on Map" button uses the Nominatim API to translate text addresses into map pins.
- **Public Visibility**: Profiles automatically display a read-only map for the public to visualize the last known location.

### 🖨️ Poster Generation Tool
- **One-Click Printing**: Generate professional, high-impact "MISSING" posters directly from a profile.
- **Smart Formatting**: A dedicated print engine strips away website clutter, leaving only the large photo, critical details, and contact info in a high-contrast layout.

### 🔍 Advanced Searching & Filtering
- **Multi-Criteria Filter**: Users can search by Name, Location, Status, or Risk Level.
- **Responsive Interface**: A modern, mobile-friendly filter bar sits atop the summary grid.

### 📋 Expanded Data Fields
- **Vulnerability Tracking**: Fields for Medical Conditions and cognitive assistance needs.
- **Visual Identification**: Ethnicity/Race fields integrated into all forms and profiles.

---

## 🛠️ 2. User Interfaces (Shortcodes)

| Shortcode | Purpose |
| :--- | :--- |
| `[mpr_public_report_form]` | Front-facing, multi-step submission form for the public. |
| `[missing_people_summary]` | Searchable grid display of all active reports. |
| `[mpr_user_profile]` | Personal dashboard for tracking followed cases and view history. |

---

## 🏗️ 3. Technical Architecture

### Custom Post Type: `missing_person`
- **Slug**: `/missing-person/`
- **Fields**: High-integrity metadata sanitized via `sanitize_text_field`.

### File Structure
- `missing-people-reporter.php`: Main core and asset enqueuing.
- `includes/meta-boxes.php`: Centralized Admin UI for all case data.
- `includes/shortcodes.php`: Logic for forms and grid displays.
- `templates/single-missing_person.php`: The primary public profile template.
- `assets/js/admin-map.js`: Logic for interactive admin mapping.
- `assets/css/print.css`: Logic for professional poster styling.

---

## ⚙️ 4. Administration

### Backend Management
Admins can manage all details from the "Missing People" menu in the WordPress sidebar. The interface is organized into logical tabs:
1. **Personal Information**: Identity and ethnicity.
2. **Physical Description**: Height, weight, hair, and medical conditions.
3. **Disappearance Details**: Date, location, and одежды.
4. **Map Location**: Interactive coordinate pinning.
5. **Authority & Contact**: Police stations, OB numbers, and family contacts.

---

## 📞 5. Future Extensibility
The plugin includes hooks (`mpr_handle_public_submission`) for integrating third-party SMS alerts or CRM systems. Developers can override aesthetics by placing custom templates in their theme's `missing-person/` directory.

---
**Version**: 1.1.0 
**Author**: Mentaltude
**URI**: https://www.missingpeople.co.ke
**Platform**: WordPress 6.x | Hestia Theme Optimization.
