<script setup lang="ts">
const technologies = [
  { name: 'PHP', icon: 'i-simple-icons-php', color: '#777BB4' },
  { name: 'Laravel', icon: 'i-simple-icons-laravel', color: '#FF2D20' },
  { name: 'Talred PHP', icon: 'i-simple-icons-php', color: '#777BB4' },
  { name: 'Nuxt', icon: 'i-simple-icons-nuxt', color: '#00DC82' },
  { name: 'TypeScript', icon: 'i-simple-icons-typescript', color: '#3178C6' },
  { name: 'Talred Identity', icon: 'i-lucide-shield-check', color: 'var(--ui-primary)' },
  { name: 'MySQL', icon: 'i-simple-icons-mysql', color: '#4479A1' },
  { name: 'Docker', icon: 'i-simple-icons-docker', color: '#2496ED' }
] as const

const accessibleStack = technologies.map(technology => technology.name).join(', ')
</script>

<template>
  <section
    class="projects-stack"
    aria-label="Technology stack"
  >
    <p class="sr-only">
      {{ accessibleStack }}
    </p>
    <div
      class="projects-stack__viewport"
      aria-hidden="true"
    >
      <div class="projects-stack__track">
        <ul
          v-for="setIndex in 2"
          :key="setIndex"
          class="projects-stack__group"
        >
          <li
            v-for="technology in technologies"
            :key="technology.name"
            class="projects-stack__item"
          >
            <UIcon
              :name="technology.icon"
              class="size-5 shrink-0"
              :style="{ color: technology.color }"
            />
            <span>{{ technology.name }}</span>
          </li>
        </ul>
      </div>
    </div>
  </section>
</template>

<style scoped>
.projects-stack {
  width: 100%;
  min-width: 0;
  margin-top: 3rem;
  border-top: 1px solid color-mix(in srgb, var(--ui-border) 70%, transparent);
  padding-top: 1.1rem;
}

.projects-stack__viewport {
  width: 100%;
  min-width: 0;
  overflow: hidden;
  mask-image: linear-gradient(to right, transparent, #000 8%, #000 92%, transparent);
}

.projects-stack__track {
  display: flex;
  width: max-content;
  animation: projects-stack-scroll 48s linear infinite;
  will-change: transform;
}

.projects-stack__group {
  display: flex;
  flex: 0 0 auto;
  align-items: center;
  gap: clamp(1.5rem, 4vw, 3.5rem);
  margin: 0;
  padding: 0 clamp(1.5rem, 4vw, 3.5rem) 0 0;
  list-style: none;
}

.projects-stack__item {
  display: inline-flex;
  flex: 0 0 auto;
  align-items: center;
  gap: 0.55rem;
  color: var(--ui-text-muted);
  font-size: 0.85rem;
  font-weight: 600;
  white-space: nowrap;
}

@keyframes projects-stack-scroll {
  from { transform: translateX(-50%); }
  to { transform: translateX(0); }
}

@media (prefers-reduced-motion: reduce) {
  .projects-stack__viewport {
    overflow: visible;
    mask-image: none;
  }

  .projects-stack__track {
    width: 100%;
    animation: none;
    transform: none;
    will-change: auto;
  }

  .projects-stack__group:first-child {
    width: 100%;
    flex-wrap: wrap;
  }

  .projects-stack__group:nth-child(2) {
    display: none;
  }
}
</style>
