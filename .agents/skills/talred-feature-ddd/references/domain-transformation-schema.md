# Domain transformation contract

The document is JSON with these top-level fields:

```json
{
  "version": 1,
  "feature": "Short feature name",
  "classification": "business",
  "context": {
    "boundedContext": "Business area",
    "outcome": "Observable business outcome",
    "actors": ["actor"],
    "scope": "tenant, project, user, or global scope"
  },
  "domain": {
    "useCases": [{"name": "Create something", "kind": "command", "preconditions": ["condition"], "result": "business result"}],
    "aggregates": [{"name": "Aggregate", "responsibility": "...", "consistencyBoundary": "..."}],
    "entities": [{"name": "Entity", "identity": "...", "responsibility": "..."}],
    "valueObjects": [{"name": "Value", "meaning": "...", "rules": ["..."]}],
    "policies": ["business or authorization policy"],
    "invariants": ["rule that must always hold"],
    "events": [{"name": "Event", "meaning": "...", "consumers": ["..."], "durable": true}],
    "readModels": [{"name": "Read model", "purpose": "...", "source": "..."}]
  },
  "boundaries": {
    "domainOwner": "service or bounded context",
    "persistence": "repository/port and owning service",
    "api": "route and contract owner",
    "bff": "server-mediated browser boundary or not-applicable",
    "client": "feature UI owner or not-applicable",
    "identity": "authorization/authentication responsibility",
    "runtime": "deployment/secret/worker responsibility or not-applicable"
  },
  "acceptanceCriteria": ["observable criterion"],
  "assumptions": [{"statement": "...", "blocking": false, "owner": "..."}],
  "decisions": ["important design decision"]
}
```

For `presentation-only`, domain collections may be empty, but the classification rationale must be included in `decisions` or `assumptions`.
