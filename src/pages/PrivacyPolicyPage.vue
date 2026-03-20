<template>
  <LegalDocumentPage :documentData="documentData" />
</template>

<script>
import LegalDocumentPage from '../components/LegalDocumentPage.vue';
import { privacyPolicyData } from '../data/legalDocuments';

export default {
  name: 'PrivacyPolicyPage',
  components: { LegalDocumentPage },
  data() {
    return {
      documentData: privacyPolicyData,
    };
  },
};
</script>