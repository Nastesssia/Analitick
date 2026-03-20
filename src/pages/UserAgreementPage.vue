<template>
  <LegalDocumentPage :documentData="documentData" />
</template>

<script>
import LegalDocumentPage from '../components/LegalDocumentPage.vue';
import { userAgreementData } from '../data/legalDocuments';

export default {
  name: 'UserAgreementPage',
  components: { LegalDocumentPage },
  data() {
    return {
      documentData: userAgreementData,
    };
  },
};
</script>