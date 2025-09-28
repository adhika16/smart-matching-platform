---
applyTo: '**'
---

# Smart Creative Matching Platform - Technical Requirements

## Project Vision
Smart Matching Platform to connect creative professionals with market opportunities or projects (freelance/job matching) using semantic search capabilities and AI-generated metadata for enhanced discoverability.

## Technical Architecture

### Core Stack (Existing)
- **Backend**: Laravel 12, Laravel Fortify (auth), SQLite database (dev)
- **Frontend**: React 19, TypeScript, TailwindCSS v4, Radix UI components
- **Build**: Vite with SSR support, Laravel Vite Plugin, Laravel Wayfinder
- **Testing**: Pest PHP testing framework

### AI & Search Integration
- **Embeddings**: AWS Bedrock models (Titan Embeddings or Claude-3 embeddings)
- **Vector Storage**: Pinecone for vector search (primary choice for MVP speed)
- **Traditional Search**: Laravel Scout + Meilisearch for keyword search
- **AI Content**: AWS Bedrock (Claude-3 models) for generative descriptions

### Database Architecture
```sql
-- Core entities with vector search integration
users (role: creative|opportunity_owner)
creative_profiles (skills, portfolio, bio_embedding)
opportunity_profiles (company_info, requirements_embedding) 
projects (description, requirements, title_embedding, description_embedding)
skills (normalized taxonomy with embeddings)
applications (creative_id, project_id, match_score)
messages (thread-based communication)
embeddings_cache (entity_type, entity_id, vector_data, model_version)
```

## MVP Feature Implementation Plan

### Phase 1: Foundation (Weeks 1-2)
**User Management & Basic Profiles**
- [ ] Dual user type authentication (creative/opportunity_owner)
- [ ] Creative profile creation (skills, portfolio upload, bio)
- [ ] Opportunity owner profiles (company verification)
- [ ] Profile completion scoring
- [ ] AWS Bedrock SDK integration setup

### Phase 2: Content & Basic Search (Weeks 3-4)
**Project Management & Traditional Search**
- [ ] Project/opportunity posting with rich editor
- [ ] Budget ranges, timelines, skill requirements
- [ ] Laravel Scout + Meilisearch integration
- [ ] Basic filtering (skills, location, budget, category)
- [ ] AWS Bedrock content generation for project descriptions

### Phase 3: Semantic Search Core (Weeks 5-6)
**Vector Search Implementation**
- [ ] AWS Bedrock embedding generation pipeline
- [ ] Pinecone integration and index setup
- [ ] Async embedding processing (Laravel Queues)
- [ ] Vector similarity matching algorithm
- [ ] Semantic search API endpoints
- [ ] Hybrid search (traditional + semantic) implementation

### Phase 4: Advanced Matching (Weeks 7-8)
**AI-Powered Recommendations & Communication**
- [ ] Match recommendation engine
- [ ] Confidence scoring for matches
- [ ] In-platform messaging system
- [ ] Application workflow (apply/invite/accept)
- [ ] Basic analytics dashboard
- [ ] Enhanced profile metadata generation

## Technical Implementation Details

### AWS Bedrock Integration
```php
// Embedding generation service
class BedrockEmbeddingService {
    public function generateEmbeddings(string $text): array
    public function generateProjectDescription(array $requirements): string
    public function enhanceProfileMetadata(array $profileData): string
}
```

### Vector Database Schema (Pinecone Example)
```json
### Vector Database Schema (Pinecone Example)
```json
{
  "index": "creative-matching",
  "namespace": "default",
  "vectors": [
    {
      "id": "job::123",
      "values": [0.1, 0.2, ...],
      "metadata": { "entity_type": "job", "job_id": 123, "category": "design" }
    },
    {
      "id": "creative_profile::456",
      "values": [0.3, 0.4, ...],
      "metadata": { "entity_type": "creative_profile", "profile_id": 456 }
    }
  ]
}
```

### Matching Algorithm Flow
1. **Input Processing**: Extract embeddings for search query/profile
2. **Vector Search**: Query Pinecone for similar vectors
3. **Hybrid Ranking**: Combine semantic similarity + traditional filters
4. **Confidence Scoring**: Calculate match probability
5. **Results Formatting**: Return ranked matches with explanations

### Laravel Integration Points
- **Jobs**: `GenerateEmbeddingsJob`, `UpdateProfileEmbeddingsJob`
- **Services**: `MatchingService`, `BedrockService`, `PineconeService` (wrapper for `probots-io/pinecone-php`)
- **Controllers**: `MatchController`, `SearchController`, `RecommendationController`
- **Commands**: `php artisan embeddings:generate`, `php artisan vectors:sync`

## Environment Configuration
```env
# AWS Bedrock
AWS_BEDROCK_REGION=us-east-1
AWS_BEDROCK_MODEL_ID=amazon.titan-embed-text-v1
AWS_BEDROCK_CONTENT_MODEL=anthropic.claude-3-sonnet-20240229-v1:0

# Pinecone Vector Database
PINECONE_API_KEY=your-pinecone-key
PINECONE_ENVIRONMENT=gcp-starter
```

## Development Priorities
1. **MVP Speed**: Use Pinecone for fastest setup, migrate to Weaviate later if needed
2. **Cost Optimization**: Cache embeddings, batch processing, rate limiting
3. **Performance**: Async processing, result caching, efficient vector queries
4. **Scalability**: Queue-based embedding generation, horizontal vector scaling

```

### Matching Algorithm Flow
1. **Input Processing**: Extract embeddings for search query/profile
2. **Vector Search**: Query Pinecone for similar vectors
3. **Hybrid Ranking**: Combine semantic similarity + traditional filters
4. **Confidence Scoring**: Calculate match probability
5. **Results Formatting**: Return ranked matches with explanations

### Laravel Integration Points
- **Jobs**: `GenerateEmbeddingsJob`, `UpdateProfileEmbeddingsJob`
- **Services**: `MatchingService`, `BedrockService`, `PineconeService`
- **Controllers**: `MatchController`, `SearchController`, `RecommendationController`
- **Commands**: `php artisan embeddings:generate`, `php artisan vectors:sync`

## Environment Configuration
```env
# AWS Bedrock
AWS_BEDROCK_REGION=us-east-1
AWS_BEDROCK_MODEL_ID=amazon.titan-embed-text-v1
AWS_BEDROCK_CONTENT_MODEL=anthropic.claude-3-sonnet-20240229-v1:0

# Pinecone Vector Database
PINECONE_API_KEY=your-pinecone-key
PINECONE_ENVIRONMENT=us-west1-gcp
```

## Development Priorities
1. **MVP Speed**: Use Pinecone for fastest setup, migrate to Weaviate later if needed
2. **Cost Optimization**: Cache embeddings, batch processing, rate limiting
3. **Performance**: Async processing, result caching, efficient vector queries
4. **Scalability**: Queue-based embedding generation, horizontal vector scaling
