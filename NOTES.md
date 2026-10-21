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

## Summary

- Generally, I don't currently work in TDD, so I’d appreciate any feedback if I violated any specific practices of this approach.
- All commits were made manually because I don't grant such permissions to the AI agent, and the rules required the TDD methodology to be reflected in the commit history.
- The code still has flaws, such as missing authorization checks for resources, which I left unaddressed.
- I only completed the tasks explicitly required in the README.md file, plus I added extra tests to cover the end-date calculation for newly created subscriptions to ensure the code fully complies with those scenario requirements.
- I also skipped unit tests for the CancelService because this code is already fully covered by functional tests.
- I didn't clean up the inconsistent naming conventions in the tests; in fact, I followed the existing pattern. I believe it would be better in the long run to group tests around resources rather than mixing resource-based and service-based groupings.
- I’d be grateful if you could explain the rather unique approach to API documentation here because, AFAIK, this can be generated automatically. Is the goal to cover every single scenario manually?
- I also don’t quite understand how you work with these spec.md files. It seems that the production code only covered 2 out of 4 scenarios thus the bug occured. In my opinion, a spec.md file should be atomic; 100% of its business logic and tests should be implemented within a single task, and only then should the completed task be merged into main branch
