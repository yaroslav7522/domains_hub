<script setup>
import VerticalNavSectionTitle from '@/@layouts/components/VerticalNavSectionTitle.vue'
import VerticalNavGroup from '@layouts/components/VerticalNavGroup.vue'
import VerticalNavLink from '@layouts/components/VerticalNavLink.vue'
</script>

<template>
  <VerticalNavLink
    :item="{
      title: 'My Domains',
      to: '/domains',
      icon: 'bx-globe',
    }"
  />
  
  <VerticalNavLink
    :item="{
      title: 'Logs',
      to: '/logs',
      icon: 'bx-align-justify',
    }"
  />
  
  <VerticalNavLink
    :item="{
      title: 'Settings',
      to: '/settings',
      icon: 'bx-cog',
    }"
  />  

</template>
