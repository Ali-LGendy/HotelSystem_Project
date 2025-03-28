<template>
  <section
    class="relative overflow-hidden bg-[url(https://thumbs.dreamstime.com/b/luxury-hotel-room-master-bedroom-creative-ai-design-background-instagram-facebook-wall-painting-backgrounds-photo-art-325040735.jpg)] bg-cover bg-center bg-no-repeat min-h-screen flex items-center justify-center"
  >
    <!-- Gradient Overlay for Better Text Contrast -->
    <div class="absolute inset-0 bg-gradient-to-b from-black/70 via-black/50 to-black/30"></div>

    <!-- Content -->
    <div class="relative z-10 p-8 md:p-12 lg:p-16 max-w-4xl mx-auto text-center">
      <!-- Animated Heading -->
      <h2
        class="text-3xl sm:text-4xl md:text-6xl lg:text-7xl font-extrabold text-white leading-tight mb-6 animate-fade-in-down"
      >
        Welcome to Our Luxury Hotel
      </h2>

      <!-- Animated Subheading -->
      <p
        class="text-lg sm:text-xl md:text-2xl text-white/90 mb-10 animate-fade-in-up delay-200"
      >
        Experience comfort and elegance in every stay
      </p>

      <!-- Animated CTA Button with Smooth Scroll -->
      <div class="mt-6 sm:mt-10">
        <a
          href="#rooms"
          @click.prevent="scrollToRooms"
          class="inline-block rounded-full bg-indigo-600 px-8 py-4 text-base sm:text-lg font-semibold text-white shadow-lg transition-all duration-300 hover:bg-indigo-700 hover:shadow-xl focus:ring-4 focus:ring-indigo-300 focus:outline-none animate-bounce-in delay-400"
        >
          Book Your Stay Today
        </a>
      </div>
    </div>
  </section>
</template>

<script>
export default {
  name: 'HeroSection',
  methods: {
    scrollToRooms(event) {
      // Prevent the default anchor behavior
      event.preventDefault();

      // Find the target section by its ID
      const targetSection = document.getElementById('rooms');
      if (targetSection) {
        // Smoothly scroll to the target section
        targetSection.scrollIntoView({
          behavior: 'smooth',
          block: 'start', // Align the top of the section with the top of the viewport
        });

        // Update the URL hash without jumping
        window.history.pushState(null, null, '#rooms');
      }
    },
  },
};
</script>

<style scoped>
/* Enable smooth scrolling for the entire page */
html {
  scroll-behavior: smooth;
}

/* Custom Animations */
@keyframes fade-in-down {
  0% {
    opacity: 0;
    transform: translateY(-20px);
  }
  100% {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes fade-in-up {
  0% {
    opacity: 0;
    transform: translateY(20px);
  }
  100% {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes bounce-in {
  0% {
    opacity: 0;
    transform: scale(0.8);
  }
  50% {
    transform: scale(1.05);
  }
  100% {
    opacity: 1;
    transform: scale(1);
  }
}

.animate-fade-in-down {
  animation: fade-in-down 1s ease-out forwards;
}

.animate-fade-in-up {
  animation: fade-in-up 1s ease-out forwards;
}

.animate-bounce-in {
  animation: bounce-in 1s ease-out forwards;
}

.delay-200 {
  animation-delay: 0.2s;
}

.delay-400 {
  animation-delay: 0.4s;
}

/* Optional: Add scroll margin to account for fixed headers */
#rooms {
  scroll-margin-top: 80px; /* Adjust based on your header height */
}
</style>