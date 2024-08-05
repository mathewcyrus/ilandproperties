# iLandRealEstate

iLandRealEstate is a real estate management application built using Symfony. It allows users to view, manage, and interact with property listings. This README provides an overview of the project, including its features, setup instructions, and how to contribute.

## Table of Contents

1. [Project Overview](#project-overview)
2. [Features](#features)
3. [Technology Stack](#technology-stack)
4. [Installation](#installation)
5. [Configuration](#configuration)
6. [Usage](#usage)
7. [Contributing](#contributing)
8. [License](#license)

## Project Overview

iLandRealEstate is designed to provide a comprehensive solution for managing real estate listings. It supports various functionalities such as viewing property details, managing property images, and user authentication.

## Features

- **User Authentication**: JWT-based authentication with secure login functionality.
- **Property Management**: CRUD operations for properties.
- **Image Management**: Ability to upload and manage images associated with properties.
- **Role-Based Access Control**: Admin roles with restricted access to certain endpoints.

## Technology Stack

- **Backend**: Symfony 5.10.2
- **Database**: MySQL with Doctrine ORM
- **Authentication**: JWT (JSON Web Token)
- **Development Tools**: Insomnia for API testing

## Installation

To get started with iLandRealEstate, follow these steps:

1. **Clone the Repository**:

   ```bash
   git clone https://github.com/your-username/iLandRealEstate.git
   cd iLandRealEstate
   ```

2. **Install Dependencies**:

   ```bash
   composer install
   ```

3. **Set Up Environment Variables**:
   Create a `.env.local` file based on `.env` and configure your database connection and other environment variables.

4. **Create the Database**:

   ```bash
   php bin/console doctrine:database:create
   ```

5. **Run Migrations**:

   ```bash
   php bin/console doctrine:migrations:migrate
   ```

6. **Start the Symfony Server**:
   ```bash
   symfony server:start
   ```

## Configuration

- **JWT Configuration**: Ensure you have configured your JWT settings in `config/packages/jwt_authentication.yaml`.
- **Database Configuration**: Update database credentials in the `.env.local` file.
- **Security Configuration**: Update role-based access control and firewall settings in `config/packages/security.yaml`.

## Usage

1. **Login**: Send a POST request to `/login` with `email` and `password` to obtain a JWT token.
2. **Access Protected Routes**: Use the JWT token in the Authorization header as `Bearer <token>` to access protected routes.
3. **Manage Properties**: Use endpoints for creating, reading, updating, and deleting properties.
4. **Manage Images**: Use endpoints for uploading and managing images associated with properties.
