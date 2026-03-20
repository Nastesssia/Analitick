<template>
  <LegalDocumentPage :documentData="documentData" />
</template>

<script>
import LegalDocumentPage from '../components/LegalDocumentPage.vue';
import { personalDataConsentData } from '../data/legalDocuments';

export default {
  name: 'PersonalDataConsentPage',
  components: { LegalDocumentPage },
  data() {
    return {
      documentData: personalDataConsentData,
    };
  },
};
</script>