let currentSlide = 0;
const slides = document.querySelectorAll('.slide');
const totalSlides = slides.length;
const slider = document.querySelector('.slider');


let slideInterval = setInterval(() => changeSlide(1), 5000);

function changeSlide(direction) {
    
    currentSlide += direction;

    
    if (currentSlide >= totalSlides) {
        currentSlide = 0;
    } else if (currentSlide < 0) {
        currentSlide = totalSlides - 1;
    }

    
    const transformValue = `translateX(-${currentSlide * 100}%)`;

    
    slider.style.transform = transformValue;

    
    resetSlideInterval();
}


document.addEventListener('keydown', (event) => {
    if (event.key === 'ArrowRight') {
        changeSlide(1);
    } else if (event.key === 'ArrowLeft') {
        changeSlide(-1);
    }
});


function resetSlideInterval() {
    clearInterval(slideInterval);
    slideInterval = setInterval(() => changeSlide(1), 7000);
}


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


window.onclick = function(event) {
    const modal = document.getElementById('movieModal');
    if (event.target === modal) {
        closeModal();
    }
};