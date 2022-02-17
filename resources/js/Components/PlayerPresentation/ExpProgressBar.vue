<template>
  <div class="progress-bar">
    <svg class="progress-bar__svg" height="195" viewBox="0 0 352 195" width="352" xmlns="http://www.w3.org/2000/svg">
      <clipPath id="clip">
        <path d="M350.819 194.62C353.404 170.153 350.828 145.417 343.257 122.008C335.686 98.5999 323.289 77.0399 306.866 58.7213C290.443 40.4027 270.36 25.733 247.914 15.6599C225.469 5.58679 201.159 0.334349 176.557 0.241903C151.955 0.149458 127.607 5.21906 105.086 15.1232C82.5653 25.0273 62.3726 39.5457 45.8126 57.7404C29.2526 75.935 16.6937 97.4013 8.94707 120.752C1.20047 144.103 -1.56143 168.82 0.839796 193.305L175.896 176.137L350.819 194.62Z" />
      </clipPath>
      <foreignObject clip-path="url(#clip)" height="195" width="352" x="0" y="0">
        <div :style="progressStyle" class="progress-bar__progress" xmlns="http://www.w3.org/1999/xhtml" />
      </foreignObject>
    </svg>
  </div>
</template>

<script>
import { mapState } from 'pinia'
import { useStore } from "@/stores/user"

export default {
  name: "ExpProgressBar",
  computed: {
    ...mapState(useStore, ['exp']),
    progressStyle() {
      const progress = (53 * this.exp) / 100;

      console.log(progress)

      return `background-image: conic-gradient(from -97deg at 49% 91%, #fff 0 ${progress}%, #111433 0);`
    }
  },
}
</script>

<style scoped>

</style>
