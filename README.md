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





## Update: EP_NONE Endpoint with Custom Rewrite Rule

### New Feature
- **Endpoint:** `/behind-the-scenes/`
- **Purpose:** Demonstrates `EP_NONE` usage with custom `add_rewrite_rule()`.
- **How it Works:**
  - EP_NONE means the endpoint is not automatically attached to WordPress default locations.
  - A manual regex rule is added to map the URL to the correct movie post.
  - Example URL:
    ```
    /movies/action/the-avengers/behind-the-scenes/
    ```
  - Shows a "🎥 Behind the Scenes" section for the selected movie.

### Related Code
- `add_rewrite_endpoint('behind-the-scenes', EP_NONE)`
- `add_rewrite_rule()` for matching `/behind-the-scenes/` URLs.
- Logic in `template_redirect` hook to display endpoint content.

### Testing
1. Go to **Settings → Permalinks → Save Changes** to flush rules.
2. Visit a movie URL with `/behind-the-scenes/` at the end.
