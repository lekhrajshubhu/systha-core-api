<template>
    <div class="pa-4">
        <div class="my-6 d-flex align-center justify-space-between">
            <div>
                <h4 class="mb-1">Document Management</h4>
                <p class="text-medium-emphasis mb-0">View and manage your uploaded profile documents.</p>
            </div>
            <div class="d-flex justify-space-between align-center mb-3">
                <v-btn color="primary" variant="flat">Upload Document</v-btn>
            </div>
        </div>
        <div class="pt-4">
            <v-table class="border rounded">
                <thead>
                    <tr>
                        <th class="text-left">Document</th>
                        <th class="text-left">Uploaded Date</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(doc, index) in documents" :key="index">
                        <td>{{ doc.name }}</td>
                        <td>{{ doc.date }}</td>
                        <td class="text-right">
                            <v-btn icon variant="text" size="small" title="View">
                                <v-icon size="18">mdi-eye-outline</v-icon>
                            </v-btn>
                            <v-btn icon variant="text" size="small" title="Download">
                                <v-icon size="18">mdi-download</v-icon>
                            </v-btn>
                        </td>
                    </tr>
                    <tr v-if="!documents.length">
                        <td colspan="3" class="text-center py-4">No documents found.</td>
                    </tr>
                </tbody>
            </v-table>
            <div class="d-flex justify-center mt-4">
                <v-btn color="primary" variant="flat" prepend-icon="mdi-content-save">Update</v-btn>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    documents: {
        type: Array,
        default: () => [],
    },
});
</script>
