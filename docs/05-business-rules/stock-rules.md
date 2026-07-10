# Stock Rules

## Stock Movement Types

1. Receiving In
2. Usage Out
3. Adjustment In
4. Adjustment Out
5. Transfer In
6. Transfer Out
7. Production Issue
8. Finished Good Receipt

## Stock Balance

1. Stock balance is maintained per outlet, per item.
2. Stock should not become negative unless approved override exists.
3. Every stock movement must have source type and source id.
4. Every stock movement must store unit cost and total cost.

## Ingredient Usage

1. Outlet records ingredient usage daily.
2. Usage date must not be in closed period.
3. Usage decreases stock.
4. Usage posts HPP journal.

## Stock Opname

1. Opname creates count session.
2. During active count, selected items/outlet may be frozen.
3. Difference creates adjustment draft.
4. Adjustment requires approval before posting stock and journal.

## Recipe

1. Recipe belongs to sellable item/menu.
2. Recipe ingredient belongs to raw material item.
3. Recipe supports waste allowance.
4. Recipe is used for standard HPP and Reconcile HPP.
