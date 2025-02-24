let currentSlide = 0; // Track the current slide index
const slides = document.querySelectorAll('.slide'); // All slides
const totalSlides = slides.length; // Total number of slides
const slider = document.querySelector('.slider'); // Cache the slider element

// Auto-slide interval ID
let slideInterval = setInterval(() => changeSlide(1), 5000); // Change slides every 5 seconds

function changeSlide(direction) {
    // Update the current slide index
    currentSlide += direction;

    // Wrap around when going out of bounds
    if (currentSlide >= totalSlides) {
        currentSlide = 0;
    } else if (currentSlide < 0) {
        currentSlide = totalSlides - 1;
    }

    // Calculate the transform value for sliding
    const transformValue = `translateX(-${currentSlide * 100}%)`;

    // Apply the transform to the slider
    slider.style.transform = transformValue;

    // Reset auto-slide when manually changed
    resetSlideInterval();
}

// Keyboard Navigation
document.addEventListener('keydown', (event) => {
    if (event.key === 'ArrowRight') {
        changeSlide(1); // Next slide
    } else if (event.key === 'ArrowLeft') {
        changeSlide(-1); // Previous slide
    }
});

// Auto-slide feature with interval reset
function resetSlideInterval() {
    clearInterval(slideInterval); // Clear existing interval
    slideInterval = setInterval(() => changeSlide(1), 7000); // Start new interval
}

// Clear interval when the page is unloaded (prevent memory leaks)
window.addEventListener('beforeunload', () => clearInterval(slideInterval));

function openModal(title, image, description, duration, genre, releaseDate) {
    document.getElementById('modalTitle').innerText = title;
    document.getElementById('modalImage').style.backgroundImage = `url('${image}')`;
    document.getElementById('modalDescription').innerText = description;
    document.getElementById('modalDuration').innerText = `Duration: ${duration} minutes`;
    document.getElementById('modalGenre').innerText = `Genre: ${genre}`;
    document.getElementById('modalReleaseDate').innerText = `Release Date: ${releaseDate}`;
    
    document.getElementById('movieModal').style.display = "block";
}

function closeModal() {
    document.getElementById('movieModal').style.display = "none";
}

// Close modal when clicking outside of it
window.onclick = function(event) {
    const modal = document.getElementById('movieModal');
    if (event.target === modal) {
        closeModal();
    }
};