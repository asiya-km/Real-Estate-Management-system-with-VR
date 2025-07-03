# Remsko Real Estate Management System

## Overview
Remsko is a robust, full-featured real estate management platform designed to streamline property listings, bookings, payments, and user management for agencies, agents, and clients. The system supports both traditional and modern real estate workflows, including advanced features like VR-enabled property tours.

## Key Features
- **Property Listings & Details**: Manage and showcase properties with rich details and images.
- **User Registration & Authentication**: Secure login and registration for buyers, sellers, agents, and admins.
- **Booking & Visit Scheduling**: Schedule property visits and manage bookings with calendar integration.
- **Payment Processing**: Supports multiple payment gateways (Chapa, CBE, Telebirr, bank transfer, and more).
- **Admin & Manager Dashboards**: Role-based access for streamlined management and oversight.
- **Activity Logs & Reporting**: Track user actions and generate insightful reports.
- **Profile Management**: Users can update and manage their profiles and documents.
- **Document Uploads**: Securely upload and manage property and user documents.
- **Calendar Integration**: Add bookings and visits directly to your calendar.
- **Responsive UI**: Modern, mobile-friendly interface for all users.
- **React Frontend**: Optional modern frontend in `/remsko-real-estate/` for enhanced user experience.
- **Virtual Reality (VR) Enabled Property Tours**: Experience immersive 360° property tours directly from the platform.

## VR-Enabled Property Tours
Remsko offers a cutting-edge VR experience for property viewing. Users can:
- Explore properties in 360° panoramas
- Access VR tours from desktop or mobile devices
- Enhance decision-making with immersive virtual walkthroughs

## Project Structure
```
/remsko
├── admin/           # Admin panel and assets
├── manager/         # Manager panel and assets
├── includes/        # Shared PHP includes (headers, footers, auth, etc.)
├── js/, css/, fonts/, images/  # Static assets
├── DATABASE FILE/   # SQL files and database scripts
├── uploads/         # Uploaded documents and images
├── remsko-real-estate/ # React frontend (optional)
├── *.php            # Main PHP application files
└── README.md        # Project documentation
```

## Getting Started
### Prerequisites
- PHP 7.x or higher
- MySQL/MariaDB
- Web server (Apache, Nginx, or XAMPP recommended)
- Node.js & npm (for React frontend)

### Installation
1. **Clone the repository**
   ```sh
   git clone https://github.com/asiya-km/Real-Estate-PHP.git
   cd Real-Estate-PHP
   ```
2. **Configure your web server** to serve the `/remsko` directory.
3. **Import the database**
   - Use `/DATABASE FILE/rems.sql` or `realestatephp.sql` to set up your database.
4. **Update configuration files**
   - Set database credentials and API keys as needed in your PHP config files.
5. **Install frontend dependencies** (optional, for React app):
   ```sh
   cd remsko-real-estate
   npm install
   npm start
   ```
6. **Access the application** via your browser (e.g., http://localhost/remsko)

## Usage
- Log in as an admin, manager, agent, or user.
- Add, edit, or browse property listings.
- Schedule and manage property visits.
- Experience VR property tours from supported listings.
- Process payments and manage bookings.

## Contributing
Contributions are welcome! Please fork the repository and submit a pull request. For major changes, open an issue first to discuss your ideas.

## License
This project is open source. See the LICENSE file for details.

## Documentation & Support
- See `project_documentation.md` and `appendix.md` for detailed technical and user documentation.
- For help, open an issue or contact the project maintainer.

---
**Remsko** – Modern Real Estate Management, VR-Ready. 