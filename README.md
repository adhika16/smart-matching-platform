# Smart Creative Matching Platform

A Laravel + Inertia.js + React application that connects creative professionals with project opportunities using AI-powered semantic search and matching.

## Features

- **Dual User Roles**: Creative professionals and opportunity owners
- **AI-Powered Matching**: Semantic search using embeddings for enhanced discoverability
- **Project Management**: Rich project posting with AI-generated descriptions
- **Authentication**: Laravel Fortify with 2FA support
- **Real-time Communication**: In-platform messaging system

## Installation

1. **Clone the repository**:
   ```bash
   git clone https://github.com/adhika16/smart-matching-platform.git
   cd smart-matching-platform
   ```

2. **Install PHP dependencies**:
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**:
   ```bash
   npm install
   ```

4. **Environment setup**:
   - Copy `.env.example` to `.env`
   - Configure database, AWS Bedrock, and Pinecone credentials
   - Generate application key: `php artisan key:generate`

5. **Database setup**:
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

6. **Build assets**:
   ```bash
   npm run build:ssr
   ```

## Usage

- **Development server**: `php artisan serve` (backend) + `npm run dev` (frontend)
- **Run tests**: `./vendor/bin/pest`
- **Code formatting**: `./vendor/bin/pint` (PHP), `npm run format` (JS/TS)

## Contributing

1. Follow Laravel and React best practices
2. Use Pest for PHP tests, maintain test coverage
3. Run linters: `npm run lint` and `./vendor/bin/pint`
4. Commit with descriptive messages

## License

MIT License
