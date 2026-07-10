# State Machine: Payment Request

```mermaid
stateDiagram-v2
    [*] --> Draft
    Draft --> Submitted
    Draft --> Cancelled
    Submitted --> Approved
    Submitted --> Rejected
    Submitted --> RevisionRequested
    RevisionRequested --> Draft
    Approved --> Paid
    Paid --> Closed
    Paid --> Reversed
```

## Transition Rules

| From | To | Actor | Rule |
|---|---|---|---|
| Draft | Submitted | Requester | Required fields and attachment valid |
| Submitted | Approved | Approver | Within approval limit |
| Submitted | Rejected | Approver | Comment required |
| Approved | Paid | Finance | Bank account selected, period open |
| Paid | Reversed | Finance Manager | Reason and approval required |
