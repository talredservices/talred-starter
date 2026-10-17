<script setup lang="ts">
const publicShellCopy = useStarterPublicPresentation()
</script>

<template>
  <div class="starter-product-theme min-h-screen">
    <a
      href="#starter-main"
      class="starter-public-skip"
    >{{ publicShellCopy.skip }}</a>
    <SaasAppHeader />

    <StarterBetaBanner />

    <UMain
      id="starter-main"
      tabindex="-1"
    >
      <slot />
    </UMain>

    <SaasAppFooter />
  </div>
</template>
