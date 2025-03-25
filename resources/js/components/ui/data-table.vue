<!-- <script setup lang="ts" generic="TData, TValue">-->
<!--// import Table from '@/components/ui/table/Table.vue'
// import TableBody from '@/components/ui/table/TableBody.vue'
// import TableCell from '@/components/ui/table/TableCell.vue'
// import TableHead from '@/components/ui/table/TableHead.vue'
// import TableHeader from '@/components/ui/table/TableHeader.vue'
// import TableRow from '@/components/ui/table/TableRow.vue'

// import {
//   FlexRender,
//   getCoreRowModel,
//   useVueTable,
//   type ColumnDef,
// } from '@tanstack/vue-table'

// const props = defineProps<{
//   columns: ColumnDef<TData, TValue>[]
//   data: TData[]
// }>()

// const table = useVueTable({
//   get data() {
//     return props.data
//   },
//   get columns() {
//     return props.columns
//   },
//   getCoreRowModel: getCoreRowModel(),
// })
// </script>

<template>
//   <div class="border rounded-md">
//     <Table>
//       <TableHeader>
//         <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
//           <TableHead v-for="header in headerGroup.headers" :key="header.id">
//             <FlexRender
//               v-if="!header.isPlaceholder"
//               :render="header.column.columnDef.header"
//               :props="header.getContext()"
//             />
//           </TableHead>
//         </TableRow>
//       </TableHeader>
//       <TableBody>
//         <template v-if="table.getRowModel().rows?.length">
//           <TableRow
//             v-for="row in table.getRowModel().rows"
//             :key="row.id"
//             :data-state="row.getIsSelected() ? 'selected' : undefined"
//           >
//             <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id">
//               <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
//             </TableCell>
//           </TableRow>
//         </template>
//         <template v-else>
//           <TableRow>
//             <TableCell :colspan="columns.length" class="h-24 text-center">
//               No results.
//             </TableCell>
//           </TableRow>
//         </template>
//       </TableBody>
//     </Table>
//   </div>
// </template>-->