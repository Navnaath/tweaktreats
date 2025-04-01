document.addEventListener("DOMContentLoaded", () => {
  // Initialize scroll reveal animations
  const scrollRevealElements = document.querySelectorAll(".scroll-reveal")

  const observerOptions = {
    threshold: 0.1,
    rootMargin: "0px 0px -50px 0px",
  }

  const scrollObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add("visible")
        scrollObserver.unobserve(entry.target)
      }
    })
  }, observerOptions)

  scrollRevealElements.forEach((element) => {
    scrollObserver.observe(element)
  })

  // Add ripple effect to buttons
  const buttons = document.querySelectorAll(".btn")

  buttons.forEach((button) => {
    button.addEventListener("click", function (e) {
      const ripple = document.createElement("div")
      ripple.classList.add("ripple-effect")

      const rect = this.getBoundingClientRect()
      const x = e.clientX - rect.left
      const y = e.clientY - rect.top

      ripple.style.left = `${x}px`
      ripple.style.top = `${y}px`

      this.appendChild(ripple)

      setTimeout(() => {
        ripple.remove()
      }, 600)
    })
  })

  // Smooth scroll to top
  const scrollToTop = document.querySelector(".scroll-to-top")
  if (scrollToTop) {
    window.addEventListener("scroll", () => {
      if (window.pageYOffset > 100) {
        scrollToTop.classList.add("visible")
      } else {
        scrollToTop.classList.remove("visible")
      }
    })

    scrollToTop.addEventListener("click", () => {
      window.scrollTo({
        top: 0,
        behavior: "smooth",
      })
    })
  }

  // Page transition
  document.addEventListener("DOMContentLoaded", () => {
    document.body.classList.add("page-loaded")
  })

  // Add hover animations to cards
  const cards = document.querySelectorAll(".card")
  cards.forEach((card) => {
    card.classList.add("hover-lift")
  })

  // Initialize loading states
  const loadingElements = document.querySelectorAll(".loading")
  loadingElements.forEach((element) => {
    element.classList.add("loading-shimmer")
  })
})

// Toast notification function
function showToast(message, type = "success") {
  const toast = document.createElement("div")
  toast.classList.add("toast", `toast-${type}`)
  toast.textContent = message

  document.body.appendChild(toast)

  setTimeout(() => {
    toast.classList.add("show")
  }, 100)

  setTimeout(() => {
    toast.classList.remove("show")
    setTimeout(() => {
      toast.remove()
    }, 300)
  }, 3000)
}

// Form validation animations
function validateForm(form) {
  const inputs = form.querySelectorAll("input, textarea")
  let isValid = true

  inputs.forEach((input) => {
    if (!input.checkValidity()) {
      input.classList.add("shake")
      isValid = false

      setTimeout(() => {
        input.classList.remove("shake")
      }, 600)
    }
  })

  return isValid
}

