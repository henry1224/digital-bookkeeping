# Aturan Stock

## Tipe Stock Movement

1. Receiving In
2. Usage Out
3. Adjustment In
4. Adjustment Out
5. Transfer In
6. Transfer Out
7. Production Issue — reserved fase 2
8. Finished Good Receipt — reserved fase 2

## Stock Balance

1. Stock balance dikelola per outlet, per item.
2. Stock tidak boleh menjadi negatif kecuali ada approved override.
3. Setiap stock movement harus memiliki source type dan source id.
4. Setiap stock movement harus menyimpan unit cost dan total cost.
5. Costing MVP memakai moving average.
6. Central Kitchen pada MVP adalah outlet/storage biasa untuk stock balance.

## Ingredient Usage

1. Outlet mencatat ingredient usage harian.
2. Usage date tidak boleh berada di closed period.
3. Usage mengurangi stock.
4. Usage memposting HPP journal.
5. Usage memakai actual quantity dan moving average cost saat posting.

## Stock Opname

1. Opname membuat count session.
2. Selama active count, item/outlet yang dipilih boleh difreeze.
3. Difference membuat adjustment draft.
4. Adjustment membutuhkan approval sebelum posting stock dan journal.
5. Posted adjustment tidak boleh diedit; koreksi memakai adjustment baru.

## Recipe

1. Recipe belongs to sellable item/menu.
2. Recipe ingredient belongs to raw material item.
3. Recipe mendukung waste allowance.
4. Recipe dipakai untuk standard HPP dan reconcile HPP.
5. Recipe tidak menggantikan actual ingredient usage untuk HPP posting MVP.

## Transfer

1. Transfer out dari outlet asal harus berpasangan dengan transfer in di outlet tujuan.
2. Transfer date tidak boleh berada di closed period pada outlet asal maupun tujuan.
3. Selisih transfer harus diselesaikan lewat adjustment yang disetujui.
