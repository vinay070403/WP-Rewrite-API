# Movies Rewrites Demo

**Description:** WordPress plugin that registers a `movie` CPT with custom permalinks (`/movies/%genre%/%postname%/`), a `reviews` endpoint, year archive rewrite rule and a custom feed `/feed/movies`.

## Features
- Custom Post Type: `movie`
- Taxonomy: `genre`
- Custom permalink with `%genre%` replacement
- Endpoint: `/reviews/` for single movie
- Rewrite rules: `/movies/year/<YEAR>/`, `/movies/genre/<slug>/`
- Custom RSS feed: `/feed/movies`

## Installation (local)
1. Clone this repo into `wp-content/plugins/`:
   ```bash
   cd wp-content/plugins
   git clone https://github.com/<your-username>/movies-rewrites-demo.git
