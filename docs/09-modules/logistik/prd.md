# Module PRD: Logistik

## Goal

Control purchase, receiving, stock, ingredient usage, recipe, opname, adjustment, and inventory reporting.

## Features

1. Outlet Request
2. Purchase Order Stock
3. Purchase Non Stock handoff to Finance payment request
4. Receiving Stock
5. Ingredient Usage
6. Stock Opname
7. Stock Adjustment
8. Transfer Antar Outlet
9. Recipe/Ingredient
10. Stock Report
11. Stock Card
12. Closing Stock

## Acceptance Criteria

1. PO dapat dibuat dari Outlet Request (converted) atau sebagai open PO langsung.
2. PO can be approved before receiving.
2. Receiving can be partial.
3. Receiving updates stock and AP.
4. Ingredient usage reduces stock and posts HPP.
5. Opname variance generates adjustment draft.
6. Approved adjustment updates stock and journal.
7. Closing stock locks inventory period.
8. Flash Cost dan Reconcile HPP detail masuk Fase 2.
