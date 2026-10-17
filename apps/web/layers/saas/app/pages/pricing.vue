<script setup lang="ts">
const localePath = useLocalePath()

await navigateTo(localePath('/about'), { redirectCode: 301 })
</script>
