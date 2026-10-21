# Notes

## API documentation

- API Platform OpenAPI document: `GET /api/docs.jsonopenapi`
- Documentation returns a JSON OpenAPI document; no Swagger UI, ReDoc, or Scalar is rendered because the Twig bundle is not installed.
- API Platform is wired in:
  - `config/routes/api_platform.yaml` (`/api` prefix)
  - `config/packages/api_platform.yaml` (`mapping.paths: ['%kernel.project_dir%/src/ApiResource']`)
- Resource definitions for the existing endpoints:
  - `src/ApiResource/OfferResource.php`
  - `src/ApiResource/CustomerResource.php`
  - `src/ApiResource/SubscriptionResource.php`
- Existing Symfony controllers/routes are referenced via `routeName` in these resources, so the documented behavior stays consistent with the live endpoints.

## Decisions

- Used API Platform metadata-only resources rather than converting controllers to API Platform resources.
- Documented required success and error responses from `openspec/specs/`.
- Wrapped OpenAPI `content` values in `ArrayObject` to satisfy API Platform's model constructor.

## What I'd do with more time

- Add automated contract tests that assert the OpenAPI document contains every path, operation, and error response.
- Add richer response schemas for success cases.
- Expose the OpenAPI document at a stable discovery URL and version it.

## Testing

`make test` is green after adding the API Platform documentation resources.
