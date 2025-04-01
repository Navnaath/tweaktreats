document.addEventListener("DOMContentLoaded", () => {
  // Mobile menu toggle
  const mobileMenuToggle = document.querySelector(".mobile-menu-toggle")
  const mobileMenu = document.querySelector(".mobile-menu")

  if (mobileMenuToggle && mobileMenu) {
    mobileMenuToggle.addEventListener("click", () => {
      mobileMenu.classList.toggle("active")
    })
  }

  // Dropdown toggle
  const dropdownToggles = document.querySelectorAll(".dropdown-toggle")

  dropdownToggles.forEach((toggle) => {
    toggle.addEventListener("click", function (e) {
      e.stopPropagation()
      const dropdown = this.closest(".dropdown")
      dropdown.classList.toggle("active")

      // Close other dropdowns
      dropdownToggles.forEach((otherToggle) => {
        if (otherToggle !== toggle) {
          otherToggle.closest(".dropdown").classList.remove("active")
        }
      })
    })
  })

  // Close dropdowns when clicking outside
  document.addEventListener("click", (e) => {
    dropdownToggles.forEach((toggle) => {
      const dropdown = toggle.closest(".dropdown")
      if (dropdown && !dropdown.contains(e.target)) {
        dropdown.classList.remove("active")
      }
    })
  })

  // Alert close button
  const alertCloseButtons = document.querySelectorAll(".alert-close")

  alertCloseButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const alert = this.closest(".alert")
      alert.style.display = "none"
    })
  })

  // Tabs
  const tabButtons = document.querySelectorAll(".tab-button")

  tabButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const tabId = this.getAttribute("data-tab")

      // Deactivate all tabs
      document.querySelectorAll(".tab-button").forEach((btn) => {
        btn.classList.remove("active")
      })

      document.querySelectorAll(".tab-content").forEach((content) => {
        content.classList.remove("active")
      })

      // Activate selected tab
      this.classList.add("active")
      document.getElementById(tabId + "-tab").classList.add("active")
    })
  })

  // Accordion
  const accordionHeaders = document.querySelectorAll(".accordion-header")

  accordionHeaders.forEach((header) => {
    header.addEventListener("click", function () {
      const accordionItem = this.closest(".accordion-item")
      accordionItem.classList.toggle("active")
    })
  })

  // Number input increment/decrement
  const numberDecrementButtons = document.querySelectorAll(".number-decrement")
  const numberIncrementButtons = document.querySelectorAll(".number-increment")

  numberDecrementButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const input = this.parentNode.querySelector("input")
      const min = Number.parseInt(input.getAttribute("min") || 0)
      let value = Number.parseInt(input.value) - 1

      if (value < min) {
        value = min
      }

      input.value = value
    })
  })

  numberIncrementButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const input = this.parentNode.querySelector("input")
      const value = Number.parseInt(input.value) + 1
      input.value = value
    })
  })

  // Range sliders
  const prepTimeSlider = document.getElementById("prep-time")
  const cookTimeSlider = document.getElementById("cook-time")

  if (prepTimeSlider) {
    prepTimeSlider.addEventListener("input", function () {
      document.getElementById("prep-time-value").textContent = this.value
    })
  }

  if (cookTimeSlider) {
    cookTimeSlider.addEventListener("input", function () {
      document.getElementById("cook-time-value").textContent = this.value
    })
  }

  // Recipe search form
  const recipeSearchForm = document.getElementById("recipe-search-form")
  const recipeResults = document.getElementById("recipe-results")

  if (recipeSearchForm) {
    recipeSearchForm.addEventListener("submit", (e) => {
      e.preventDefault()

      const query = document.getElementById("recipe-query").value

      if (!query.trim()) {
        return
      }

      // Show loading state
      recipeResults.innerHTML = '<div class="placeholder-message">Searching...</div>'

      // Fetch results
      fetch("api/search-recipes.php?query=" + encodeURIComponent(query))
        .then((response) => response.json())
        .then((data) => {
          if (data.length === 0) {
            recipeResults.innerHTML =
              '<div class="placeholder-message">No recipes found. Try a different search term.</div>'
            return
          }

          let html = ""

          data.forEach((recipe) => {
            html += `
                            <div class="recipe-card">
                                <div class="recipe-header">
                                    <h3>${recipe.name}</h3>
                                    <button class="btn btn-icon btn-ghost recipe-expand" data-id="${recipe.id}">
                                        <i class="fas fa-chevron-down"></i>
                                    </button>
                                </div>
                                <div class="recipe-content">
                                    <p>${recipe.description}</p>
                                    <div class="recipe-details" id="recipe-details-${recipe.id}" style="display: none;">
                                        <div class="recipe-section">
                                            <h4>Ingredients:</h4>
                                            <ul>
                                                ${recipe.ingredients.map((ingredient) => `<li>${ingredient}</li>`).join("")}
                                            </ul>
                                        </div>
                                        <div class="recipe-section">
                                            <h4>Instructions:</h4>
                                            <ol>
                                                ${recipe.instructions.map((step) => `<li>${step}</li>`).join("")}
                                            </ol>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `
          })

          recipeResults.innerHTML = html

          // Add event listeners to expand buttons
          document.querySelectorAll(".recipe-expand").forEach((button) => {
            button.addEventListener("click", function () {
              const recipeId = this.getAttribute("data-id")
              const detailsElement = document.getElementById("recipe-details-" + recipeId)

              if (detailsElement.style.display === "none") {
                detailsElement.style.display = "block"
                this.innerHTML = '<i class="fas fa-chevron-up"></i>'
              } else {
                detailsElement.style.display = "none"
                this.innerHTML = '<i class="fas fa-chevron-down"></i>'
              }
            })
          })
        })
        .catch((error) => {
          console.error("Error searching recipes:", error)
          recipeResults.innerHTML = '<div class="placeholder-message">An error occurred. Please try again.</div>'
        })
    })
  }

  // Measurement converter form
  const measurementForm = document.getElementById("measurement-form")
  const conversionResult = document.getElementById("conversion-result")

  if (measurementForm) {
    measurementForm.addEventListener("submit", (e) => {
      e.preventDefault()

      const amount = document.getElementById("amount").value
      const unit = document.getElementById("unit").value
      const ingredient = document.getElementById("ingredient").value

      if (!amount || !unit || !ingredient) {
        return
      }

      // Show loading state
      conversionResult.innerHTML = "Converting..."
      conversionResult.classList.add("active")

      // Fetch conversion
      fetch("api/convert-measurement.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `amount=${amount}&unit=${unit}&ingredient=${ingredient}`,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            conversionResult.innerHTML = `${amount} ${unit} of ${document.getElementById("ingredient").options[document.getElementById("ingredient").selectedIndex].text} = ${data.grams} grams`
          } else {
            conversionResult.innerHTML = data.message || "Error converting measurement. Please try again."
          }
        })
        .catch((error) => {
          console.error("Error converting measurement:", error)
          conversionResult.innerHTML = "An error occurred. Please try again."
        })
    })
  }

  // Recipe adjust form
  const recipeAdjustForm = document.getElementById("recipe-adjust-form")
  const adjustedRecipe = document.getElementById("adjusted-recipe")

  if (recipeAdjustForm) {
    recipeAdjustForm.addEventListener("submit", (e) => {
      e.preventDefault()

      const recipeText = document.getElementById("recipe-text").value
      const originalServings = document.getElementById("original-servings").value
      const targetServings = document.getElementById("target-servings").value

      if (!recipeText.trim() || !originalServings || !targetServings) {
        return
      }

      // Show loading state
      adjustedRecipe.innerHTML = '<div class="placeholder-message">Adjusting recipe...</div>'
      adjustedRecipe.classList.add("active")

      // Fetch adjusted recipe
      fetch("api/adjust-recipe.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `recipe_text=${encodeURIComponent(recipeText)}&original_servings=${originalServings}&target_servings=${targetServings}`,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            const html = `
                            <div class="recipe-section">
                                <h3>Adjusted Ingredients</h3>
                                <ul>
                                    ${data.ingredients.map((ingredient) => `<li>${ingredient.amount} ${ingredient.unit} ${ingredient.name}${ingredient.grams ? ` (${ingredient.grams}g)` : ""}</li>`).join("")}
                                </ul>
                            </div>
                            <div class="recipe-section">
                                <h3>Instructions</h3>
                                <ol>
                                    ${data.instructions.map((instruction) => `<li>${instruction}</li>`).join("")}
                                </ol>
                            </div>
                        `

            adjustedRecipe.innerHTML = html
          } else {
            adjustedRecipe.innerHTML =
              '<div class="placeholder-message">' +
              (data.message || "Error adjusting recipe. Please try again.") +
              "</div>"
          }
        })
        .catch((error) => {
          console.error("Error adjusting recipe:", error)
          adjustedRecipe.innerHTML = '<div class="placeholder-message">An error occurred. Please try again.</div>'
        })
    })
  }

  // Image upload and preview
  const imageDropArea = document.getElementById("image-drop-area")
  const imageInput = document.getElementById("image-input")
  const imagePreview = document.getElementById("image-preview")
  const imagePreviewImg = imagePreview ? imagePreview.querySelector("img") : null
  const imageRemoveButton = document.querySelector(".image-remove")
  const extractRecipeButton = document.getElementById("extract-recipe")
  const extractedTextContainer = document.getElementById("extracted-text-container")
  const extractedText = document.getElementById("extracted-text")

  if (imageDropArea && imageInput) {
    // Click to select file
    imageDropArea.addEventListener("click", (e) => {
      if (e.target !== imageRemoveButton && !imageRemoveButton.contains(e.target)) {
        imageInput.click()
      }
    })

    // Handle file selection
    imageInput.addEventListener("change", function () {
      if (this.files && this.files[0]) {
        const file = this.files[0]
        const reader = new FileReader()

        reader.onload = (e) => {
          imagePreviewImg.src = e.target.result
          imagePreview.classList.remove("hidden")
          document.querySelector(".drop-message").classList.add("hidden")
        }

        reader.readAsDataURL(file)
      }
    })

    // Handle drag and drop
    imageDropArea.addEventListener("dragover", function (e) {
      e.preventDefault()
      this.classList.add("active")
    })

    imageDropArea.addEventListener("dragleave", function () {
      this.classList.remove("active")
    })

    imageDropArea.addEventListener("drop", function (e) {
      e.preventDefault()
      this.classList.remove("active")

      if (e.dataTransfer.files && e.dataTransfer.files[0]) {
        imageInput.files = e.dataTransfer.files

        const file = e.dataTransfer.files[0]
        const reader = new FileReader()

        reader.onload = (e) => {
          imagePreviewImg.src = e.target.result
          imagePreview.classList.remove("hidden")
          document.querySelector(".drop-message").classList.add("hidden")
        }

        reader.readAsDataURL(file)
      }
    })

    // Remove image
    if (imageRemoveButton) {
      imageRemoveButton.addEventListener("click", (e) => {
        e.stopPropagation()
        imageInput.value = ""
        imagePreview.classList.add("hidden")
        document.querySelector(".drop-message").classList.remove("hidden")

        if (extractedTextContainer) {
          extractedTextContainer.classList.add("hidden")
        }

        if (extractedText) {
          extractedText.value = ""
        }
      })
    }

    // Extract recipe from image
    if (extractRecipeButton) {
      extractRecipeButton.addEventListener("click", function () {
        if (!imageInput.files || !imageInput.files[0]) {
          return
        }

        const file = imageInput.files[0]
        const formData = new FormData()
        formData.append("image", file)

        // Show loading state
        this.disabled = true
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Extracting...'

        // Extract recipe
        fetch("api/extract-recipe.php", {
          method: "POST",
          body: formData,
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.success) {
              extractedText.value = data.text
              extractedTextContainer.classList.remove("hidden")
            } else {
              alert(data.message || "Error extracting recipe. Please try again.")
            }
          })
          .catch((error) => {
            console.error("Error extracting recipe:", error)
            alert("An error occurred. Please try again.")
          })
          .finally(() => {
            this.disabled = false
            this.innerHTML = '<i class="fas fa-upload"></i> Extract Recipe'
          })
      })
    }
  }

  // Voice recording
  const startRecordingButton = document.getElementById("start-recording")
  const stopRecordingButton = document.getElementById("stop-recording")
  const recorderStatus = document.getElementById("recorder-status")
  const transcribedTextContainer = document.getElementById("transcribed-text-container")
  const transcribedText = document.getElementById("transcribed-text")

  let mediaRecorder
  let audioChunks = []
  let recordingTimer
  let recordingTime = 0

  if (startRecordingButton && stopRecordingButton) {
    startRecordingButton.addEventListener("click", () => {
      // Request microphone access
      navigator.mediaDevices
        .getUserMedia({ audio: true })
        .then((stream) => {
          // Create media recorder
          mediaRecorder = new MediaRecorder(stream)
          audioChunks = []

          // Collect audio chunks
          mediaRecorder.addEventListener("dataavailable", (e) => {
            audioChunks.push(e.data)
          })

          // Handle recording stop
          mediaRecorder.addEventListener("stop", () => {
            // Stop all tracks
            stream.getTracks().forEach((track) => track.stop())

            // Create audio blob
            const audioBlob = new Blob(audioChunks, { type: "audio/wav" })

            // Transcribe audio
            const formData = new FormData()
            formData.append("audio", audioBlob)

            // Show loading state
            recorderStatus.innerHTML = `
                            <div class="recorder-icon">
                                <i class="fas fa-spinner fa-spin"></i>
                            </div>
                            <p>Transcribing...</p>
                        `

            // Transcribe audio
            fetch("api/transcribe-voice.php", {
              method: "POST",
              body: formData,
            })
              .then((response) => response.json())
              .then((data) => {
                if (data.success) {
                  transcribedText.value = data.text
                  transcribedTextContainer.classList.remove("hidden")

                  recorderStatus.innerHTML = `
                                        <div class="recorder-icon">
                                            <i class="fas fa-microphone"></i>
                                        </div>
                                        <p>Ready to record</p>
                                    `
                } else {
                  alert(data.message || "Error transcribing audio. Please try again.")

                  recorderStatus.innerHTML = `
                                        <div class="recorder-icon">
                                            <i class="fas fa-microphone"></i>
                                        </div>
                                        <p>Ready to record</p>
                                    `
                }
              })
              .catch((error) => {
                console.error("Error transcribing audio:", error)
                alert("An error occurred. Please try again.")

                recorderStatus.innerHTML = `
                                    <div class="recorder-icon">
                                        <i class="fas fa-microphone"></i>
                                    </div>
                                    <p>Ready to record</p>
                                `
              })
          })

          // Start recording
          mediaRecorder.start()

          // Update UI
          startRecordingButton.classList.add("hidden")
          stopRecordingButton.classList.remove("hidden")

          recorderStatus.innerHTML = `
                        <div class="recorder-icon recording">
                            <i class="fas fa-microphone"></i>
                        </div>
                        <p class="recording-time">00:00</p>
                    `

          // Start timer
          recordingTime = 0
          recordingTimer = setInterval(() => {
            recordingTime++
            const minutes = Math.floor(recordingTime / 60)
              .toString()
              .padStart(2, "0")
            const seconds = (recordingTime % 60).toString().padStart(2, "0")
            document.querySelector(".recording-time").textContent = `${minutes}:${seconds}`
          }, 1000)
        })
        .catch((error) => {
          console.error("Error accessing microphone:", error)
          alert("Error accessing microphone. Please make sure you have granted microphone permissions.")
        })
    })

    stopRecordingButton.addEventListener("click", () => {
      if (mediaRecorder && mediaRecorder.state !== "inactive") {
        // Stop recording
        mediaRecorder.stop()

        // Clear timer
        clearInterval(recordingTimer)

        // Update UI
        startRecordingButton.classList.remove("hidden")
        stopRecordingButton.classList.add("hidden")
      }
    })
  }

  // Favorite buttons
  const favoriteButtons = document.querySelectorAll(".favorite-btn")

  favoriteButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const recipeId = this.getAttribute("data-id")

      // Toggle favorite
      fetch("api/toggle-favorite.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `recipe_id=${recipeId}`,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            if (data.favorited) {
              this.classList.add("active")
              this.innerHTML = '<i class="fas fa-heart"></i>'
            } else {
              this.classList.remove("active")
              this.innerHTML = '<i class="far fa-heart"></i>'
            }
          } else {
            if (data.message === "Not logged in") {
              window.location.href = "login.php?redirect=" + encodeURIComponent(window.location.pathname)
            } else {
              alert(data.message || "Error toggling favorite. Please try again.")
            }
          }
        })
        .catch((error) => {
          console.error("Error toggling favorite:", error)
          alert("An error occurred. Please try again.")
        })
    })
  })

  // Approve submission buttons
  const approveButtons = document.querySelectorAll(".approve-submission")

  approveButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const submissionId = this.getAttribute("data-id")

      if (!confirm("Are you sure you want to approve this submission?")) {
        return
      }

      // Approve submission
      fetch("api/approve-submission.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `submission_id=${submissionId}`,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            // Update UI
            const submissionCard = this.closest(".submission-card")
            const badge = submissionCard.querySelector(".badge")

            badge.classList.remove("badge-outline")
            badge.classList.add("badge-success")
            badge.textContent = "Verified"

            // Remove admin actions
            const adminActions = submissionCard.querySelector(".admin-actions")
            if (adminActions) {
              adminActions.remove()
            }
          } else {
            alert(data.message || "Error approving submission. Please try again.")
          }
        })
        .catch((error) => {
          console.error("Error approving submission:", error)
          alert("An error occurred. Please try again.")
        })
    })
  })

  // Density correction form
  const densityCorrectionForm = document.getElementById("density-correction-form")

  if (densityCorrectionForm) {
    densityCorrectionForm.addEventListener("submit", (e) => {
      e.preventDefault()

      const ingredientName = document.getElementById("ingredient-name").value
      const densityCup = document.getElementById("density-cup").value
      const densityTablespoon = document.getElementById("density-tablespoon").value
      const densityTeaspoon = document.getElementById("density-teaspoon").value

      if (!ingredientName.trim() || !densityCup || !densityTablespoon || !densityTeaspoon) {
        return
      }

      // Submit correction
      fetch("api/submit-density-correction.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `ingredient_name=${encodeURIComponent(ingredientName)}&density_cup=${densityCup}&density_tablespoon=${densityTablespoon}&density_teaspoon=${densityTeaspoon}`,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            alert("Density correction submitted successfully!")
            densityCorrectionForm.reset()
          } else {
            if (data.message === "Not logged in") {
              if (confirm("You need to be logged in to submit corrections. Would you like to log in now?")) {
                window.location.href = "login.php?redirect=" + encodeURIComponent(window.location.pathname)
              }
            } else {
              alert(data.message || "Error submitting correction. Please try again.")
            }
          }
        })
        .catch((error) => {
          console.error("Error submitting correction:", error)
          alert("An error occurred. Please try again.")
        })
    })
  }
})

