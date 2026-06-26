// ---------------------------------------------------------------------------------
// GLOBAL UTILITIES
// ---------------------------------------------------------------------------------

/**
 * Transforms various URL formats (like Google Drive) into direct image links.
 * This allows users to paste standard sharing links and have them work as images.
 * @param {string} url - The original URL.
 * @returns {string} - The direct image link.
 */
function transformImageURL(url) {
    if (!url) return 'pictures/placeholder.jpg';
    
    // Clean up spaces and trim
    url = url.trim();

    // Google Drive Link Transformation (Reliable Endpoint)
    if (url.includes('drive.google.com')) {
        const fileIdMatch = url.match(/\/d\/([a-zA-Z0-9_-]+)/) || url.match(/id=([a-zA-Z0-9_-]+)/);
        if (fileIdMatch && fileIdMatch[1]) {
            // Using the higher-performance content delivery endpoint
            return `https://lh3.googleusercontent.com/d/${fileIdMatch[1]}`;
        }
    }
    
    // Dropbox Transformation
    // Standard: https://www.dropbox.com/s/xyz/img.jpg?dl=0
    // Direct: https://dl.dropboxusercontent.com/s/xyz/img.jpg
    if (url.includes('dropbox.com')) {
        return url.replace('www.dropbox.com', 'dl.dropboxusercontent.com').replace('?dl=0', '');
    }

    return url;
}

// ---------------------------------------------------------------------------------
// BOOKING FORM LOGIC
// ---------------------------------------------------------------------------------

/**
 * Scrolls to the booking form and pre-selects a tour from the dropdown.
 * This function is typically called from "Book Now" buttons on specific tour cards.
 * @param {string} tourName - The value of the tour option to be selected.
 */
function bookTour(tourName) {
    // Scroll the booking section into the viewport smoothly.
    document.getElementById('booking').scrollIntoView({ behavior: 'smooth', block: 'start' });

    const tourSelect = document.getElementById('tour-select');
    
    // Iterate through the dropdown options to find and select the matching tour.
    for (let i = 0; i < tourSelect.options.length; i++) {
        if (tourSelect.options[i].value === tourName) {
            tourSelect.selectedIndex = i;
            break;
        }
    }
}

// ---------------------------------------------------------------------------------
// EXPANDABLE TOUR DESCRIPTIONS WITH IMAGE ANIMATION
// ---------------------------------------------------------------------------------

/**
 * Initialize clickable tour descriptions.
 * Click description to expand/collapse.
 * Image shrinks up when description expands.
 */
function initializeTourDescriptions() {
    const descriptions = document.querySelectorAll('.description-wrapper');
    
    descriptions.forEach((desc) => {
        desc.addEventListener('click', function(e) {
            e.stopPropagation();
            
            const tourCard = this.closest('.tour-card');
            const isExpanded = this.classList.contains('expanded');
            
            if (isExpanded) {
                // Collapse
                this.classList.remove('expanded');
                tourCard.classList.remove('expanded');
            } else {
                // Expand
                this.classList.add('expanded');
                tourCard.classList.add('expanded');
                
                // Smooth scroll to keep card visible
                setTimeout(() => {
                    tourCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 200);
            }
        });
    });
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    initializeTourDescriptions();
    fetchAndRenderTours();
});

function fetchAndRenderTours() {
    fetch('api/get_tours.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.tours && data.tours.length > 0) {
                const grid = document.querySelector('#tours .grid');
                if (!grid) return;
                
                // Clear the static placeholders
                grid.innerHTML = '';
                
                const selects = document.querySelectorAll('select#tour-select');
                selects.forEach(select => {
                    select.innerHTML = '<option value="">Choose your tour</option>';
                });

                data.tours.forEach((tour, index) => {
                    // Populate Dropdown
                    selects.forEach(select => {
                        const option = document.createElement('option');
                        option.value = tour.title;
                        option.textContent = tour.title;
                        select.appendChild(option);
                    });

                    // Build Card
                    const delay = index % 7;
                    const delayClass = delay > 0 ? `delay-${delay}` : '';
                    const safeTitle = tour.title.replace(/'/g, "\\'");
                    
                    let icon = 'fa-compass';
                    const catLower = (tour.category || '').toLowerCase();
                    if (catLower.includes('water') || catLower.includes('blue') || catLower.includes('ocean')) icon = 'fa-water';
                    else if (catLower.includes('safari') || catLower.includes('nature') || catLower.includes('wildlife')) icon = 'fa-leaf';
                    else if (catLower.includes('culture') || catLower.includes('history')) icon = 'fa-gopuram';
                    else if (catLower.includes('dining') || catLower.includes('romance')) icon = 'fa-utensils';

                    const tourImg = transformImageURL(tour.image_url);

                    const cardHtml = `
                        <div class="tour-card group animated-item scroll-animate-up ${delayClass}" data-category="${tour.category || 'Category'}">
                            <div class="image-container">
                                <img src="${tourImg}" alt="${tour.title}" class="tour-card-img" onerror="this.onerror=null; this.src='https://placehold.co/600x400/1e1b4b/ffffff?text=${encodeURIComponent(tour.title)}';">
                            </div>
                            <div class="tour-card-content">
                                <div class="tour-card-body">
                                    <h3>${tour.title}</h3>
                                    <div class="description-wrapper" style="cursor: pointer;">
                                        <p>${tour.description}</p>
                                    </div>
                                </div>
                                <div class="tour-card-footer">
                                    <div class="meta-row">
                                        <span><i class="far fa-clock"></i> ${tour.duration || 'Flexible'}</span>
                                        <span><i class="fas ${icon}"></i> ${tour.category || 'Tour'}</span>
                                    </div>
                                    <button type="button" onclick="bookTour('${safeTitle}')" class="tour-card-btn">Book Now</button>
                                </div>
                            </div>
                        </div>
                    `;
                    grid.insertAdjacentHTML('beforeend', cardHtml);
                });

                // Re-initialize description listeners for new dynamic content
                initializeTourDescriptions();
                
                // Re-run the observer on new items
                const newItems = grid.querySelectorAll('.animated-item');
                const observer = new IntersectionObserver((entries, observer) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('is-visible');
                            observer.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.1 });
                
                newItems.forEach(item => observer.observe(item));
            } else {
                // If no tours or error, show a friendly message
                const grid = document.querySelector('#tours .grid');
                if (grid) grid.innerHTML = '<p class="text-center col-span-full py-10 opacity-60">Adventure loading failed. Please refresh or check back later.</p>';
                console.error("Server returned success:false or empty tours:", data.message);
            }
        })
        .catch(err => {
            const grid = document.querySelector('#tours .grid');
            if (grid) grid.innerHTML = '<p class="text-center col-span-full py-10 opacity-60">Connection error. Please refresh the page.</p>';
            console.error("Error loading tours:", err);
        });
}

// --- Form Submission Handler ---

// Get references to the form and message boxes for later use.
const form = document.getElementById('booking-form');
const errorBox = document.getElementById('error-message');
const successBox = document.getElementById('success-message');

/**
 * Displays the error message box with a specific message.
 * @param {string} text - The error message to display.
 */
function showError(text) {
    document.getElementById('error-text').textContent = text;
    errorBox.classList.remove('hidden');
    // Scroll to the error message so the user sees it.
    errorBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

/**
 * Hides the error message box.
 */
function hideError() {
    errorBox.classList.add('hidden');
}

// Attach a 'submit' event listener to the booking form.
if (form) {
    form.addEventListener('submit', (event) => {
        // Prevent the default browser form submission behavior.
        event.preventDefault();
        
        let isValid = true;
        
        // --- Step 1: Validate the tour dropdown ---
        const tourSelect = document.getElementById('tour-select');
        if (tourSelect.value === "") {
            isValid = false;
            // Add a red border to indicate an error.
            tourSelect.classList.add('border-2', 'border-red-500', 'rounded-lg');
        } else {
            tourSelect.classList.remove('border-2', 'border-red-500', 'rounded-lg');
        }

        // --- Step 2: Validate all other inputs with the 'required' attribute ---
        const requiredInputs = form.querySelectorAll('[required]');
        requiredInputs.forEach(input => {
            // Check for empty values or non-positive numbers.
            if (!input.value || (input.type === 'number' && parseInt(input.value) <= 0)) {
                isValid = false;
                input.classList.add('border-red-500');
            } else {
                input.classList.remove('border-red-500');
            }
        });

        // --- Step 3: If form is invalid, show an error and stop ---
        if (!isValid) {
            showError("Please fill out all required fields, including selecting at least one tour.");
            return;
        }

        // If validation passes, hide any previous error messages.
        hideError();

        // --- Step 4: Prepare booking details ---
        const formData = new FormData(form);
        const templateParams = {
            tour: formData.get('tour'),
            name: formData.get('name'),
            email: formData.get('email'),
            phone: formData.get('phone'),
            guests: formData.get('guests'),
            date: formData.get('date'),
            requests: formData.get('requests') || 'N/A' // Use 'N/A' if requests are empty.
        };

        // --- Step 5: Send to our Database API ---
        const submitButton = form.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.textContent;
        submitButton.textContent = 'Processing...'; // Provide user feedback.
        submitButton.disabled = true;

        fetch('api/book.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(templateParams)
        })
        .then(res => res.json())
        .then(dbRes => {
            if (dbRes.success) {
                // --- On Success ---
                console.log('DATABASE:', dbRes.message);
                
                // WhatsApp redirect removed as per user request
                
                form.style.display = 'none'; // Hide the form.
                successBox.classList.remove('hidden'); // Show the success message.
                document.getElementById('booking').scrollIntoView({ behavior: 'smooth', block: 'start' });
            } else {
                // --- On Server Logic Error ---
                showError("Server Error: " + (dbRes.message || "Failed to process booking."));
            }
        })
        .catch(error => {
            // --- On Failure ---
            showError("Failed to send booking request. Please try again later.");
            console.error(error);
        })
        .finally(() => {
            // --- Always runs after success or failure ---
            submitButton.textContent = originalButtonText;
            submitButton.disabled = false;
        });
    });
}


// ---------------------------------------------------------------------------------
// GENERAL UI/UX (Navbar and Mobile Menu)
// ---------------------------------------------------------------------------------

/**
 * Applies a glassmorphism effect to the navbar when the user scrolls down.
 */



/**
 * Toggles the visibility of the mobile navigation menu with animation.
 * Also handles closing the menu when clicking outside of it or on a menu link.
 */
const btn = document.getElementById('hamburger-btn');
const menu = document.getElementById('mobile-menu');

if (btn && menu) {
    // Open/close the menu when the hamburger button is clicked
    btn.addEventListener('click', (e) => {
        e.stopPropagation(); // Prevent this click from being caught by the document listener
        menu.classList.toggle('menu-open');
    });

    // Add a global click listener to close the menu when clicking away
    document.addEventListener('click', (e) => {
        if (menu.classList.contains('menu-open')) {
            const isClickInsideMenu = menu.contains(e.target);
            const isClickOnButton = btn.contains(e.target);

            // If the click is not inside the menu and not on the button, close the menu
            if (!isClickInsideMenu && !isClickOnButton) {
                menu.classList.remove('menu-open');
            }
        }
    });

    // Close the menu when a link inside it is clicked
    const mobileLinks = document.querySelectorAll('#mobile-menu a');
    mobileLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (menu.classList.contains('menu-open')) {
                menu.classList.remove('menu-open');
            }
        });
    });
}

// ---------------------------------------------------------------------------------
// (Gallery Logic Removed)
// ---------------------------------------------------------------------------------



// ---------------------------------------------------------------------------------
// TESTIMONIALS SECTION LOGIC
// ---------------------------------------------------------------------------------

const allTestimonials = [
    { name: "Eleanor Vance", text: "Zanzibar Safari completely exceeded our expectations! From floating in the turquoise waters on the Safari Blue to exploring the narrow streets of Stone Town, every moment was pure magic.", img: "https://i.pravatar.cc/150?u=eleanor", rating: 5, platform: "tripadvisor", date: "2 days ago" },
    { name: "James & Claire", text: "The sunset dhow cruise was the most romantic experience of our lives. The luxurious setup, the warm breeze, and the incredibly knowledgeable guides made our honeymoon unforgettable.", img: "https://i.pravatar.cc/150?u=james", rating: 5, platform: "google", date: "1 week ago" },
    { name: "Sophia Martinez", text: "We booked the deep forest expedition and were blown away by the beauty of Masingini. Seeing the wild Red Colobus monkeys surrounded by lush emerald canopies was a breathtaking adventure.", img: "https://i.pravatar.cc/150?u=sophia", rating: 5, platform: "tripadvisor", date: "3 days ago" },
    { name: "Marcus Thorne", text: "Absolute perfection. Their fleet of luxury vehicles ensured we traveled the island in ultimate comfort, and their local insight gave us access to pristine coastal spots we never would have discovered alone.", img: "https://i.pravatar.cc/150?u=marcus", rating: 5, platform: "google", date: "Yesterday" },
    { name: "Lily Chen", text: "Swimming with dolphins near Mnemba was a dream come true. The team's dedication to sustainable, safe tourism gave us incredible peace of mind.", img: "https://i.pravatar.cc/150?u=lily", rating: 5, platform: "tripadvisor", date: "2 weeks ago" },
    { name: "Oliver Scott", text: "Every mile was luxurious. The seamless booking, transparent pricing, and unparalleled coastal horseback ride proved they are the absolute best on the island.", img: "https://i.pravatar.cc/150?u=oliver", rating: 5, platform: "google", date: "1 month ago" }
];

// --- State variables for the testimonial functionality ---
let currentTestimonials = []; // The 3 testimonials currently on display.
let displayedCardElements = []; // The actual DOM elements for the displayed cards.
let availableTestimonialsPool = [...allTestimonials]; // Testimonials available to be shown.
const testimonialGrid = document.getElementById('testimonial-grid');
const testimonialPrevBtn = document.getElementById('testimonial-prev');
const testimonialNextBtn = 'testimonial-next';
let autoChangeInterval; // Holds the reference to the `setInterval` timer.

/**
 * Creates the HTML string for a single testimonial card.
 * @param {object} testimonial - The testimonial data object.
 * @returns {string} The HTML content for the card.
 */
function createTestimonialCardHTML(testimonial) {
    const starRating = '<div class="flex gap-0.5 mb-3 text-[10px]">' +
                       Array(testimonial.rating).fill('<i class="fas fa-star text-brand-accent"></i>').join('') +
                       Array(5 - testimonial.rating).fill('<i class="far fa-star text-brand-accent"></i>').join('') +
                       '</div>';

    const platformIcon = testimonial.platform === 'google' 
        ? '<i class="fa-brands fa-google text-blue-500"></i>' 
        : '<i class="fa-brands fa-tripadvisor text-[#00aa6c]"></i>';
    
    const platformName = testimonial.platform === 'google' ? 'Google Review' : 'TripAdvisor';

    return `
        <div class="flex justify-between items-start mb-4">
            <div class="flex gap-3 items-center">
                <img src="${testimonial.img}" alt="${testimonial.name}" class="w-10 h-10 rounded-full object-cover border border-gray-100 shadow-sm">
                <div>
                    <h4 class="text-sm font-bold text-gray-800 leading-tight">${testimonial.name}</h4>
                    <p class="text-[10px] text-gray-400 font-medium">${testimonial.date}</p>
                </div>
            </div>
            <div class="text-sm opacity-80">${platformIcon}</div>
        </div>
        
        <div class="mb-3">
            ${starRating}
            <p class="text-xs leading-relaxed text-gray-600 font-light italic">"${testimonial.text}"</p>
        </div>

        <div class="flex items-center gap-1.5 pt-3 border-t border-gray-50 mt-auto">
            <div class="w-1.5 h-1.5 rounded-full bg-emerald-500"></div>
            <span class="text-[9px] uppercase tracking-widest font-bold text-gray-400">Verified ${platformName}</span>
        </div>
    `;
}

/**
 * Updates the content of a specific card element with a new testimonial.
 * @param {HTMLElement} cardElement - The DOM element of the card to update.
 *
 * @param {object} newTestimonial - The new testimonial data to display.
 */
function updateCardContent(cardElement, newTestimonial) {
    cardElement.innerHTML = createTestimonialCardHTML(newTestimonial);
    cardElement.dataset.testimonialId = newTestimonial.name;
    cardElement.classList.remove('fade-out'); // Remove fade-out class to make it visible again.
}

/**
 * Sets up the initial 3 testimonial cards on page load.
 */
function initializeTestimonials() {
    if (!testimonialGrid) return;
    testimonialGrid.innerHTML = ''; // Clear any existing cards.
    displayedCardElements = [];

    // Shuffle the pool to get a random starting set.
    const shuffledPool = [...allTestimonials].sort(() => 0.5 - Math.random());
    
    currentTestimonials = shuffledPool.slice(0, 3);
    availableTestimonialsPool = shuffledPool.slice(3);

    // Create and append the initial cards.
    currentTestimonials.forEach(testimonial => {
        const cardWrapper = document.createElement('div');
        cardWrapper.classList.add('testimonial-card', 'animated-item');
        updateCardContent(cardWrapper, testimonial);
        testimonialGrid.appendChild(cardWrapper);
        displayedCardElements.push(cardWrapper);
    });
}

/**
 * Starts the timer for auto-rotating testimonials.
 */
function startAutoChange() {
    if (autoChangeInterval) clearInterval(autoChangeInterval);
    autoChangeInterval = setInterval(() => updateSingleTestimonial(), 5000); // 5 seconds
}

/**
 * Stops the auto-rotation timer.
 */
function stopAutoChange() {
    if (autoChangeInterval) clearInterval(autoChangeInterval);
}

/**
 * Replaces one of the visible testimonials with a new one from the pool.
 * @param {number} manualDirection - 0 for auto, 1 for next, -1 for prev.
 */
function updateSingleTestimonial(manualDirection = 0) {
    stopAutoChange(); // Pause during the update.

    // Pick a random card to replace for auto-mode.
    let cardToReplaceIndex = Math.floor(Math.random() * displayedCardElements.length);
    
    const cardElementToReplace = displayedCardElements[cardToReplaceIndex];

    // Refill the pool if it's empty to ensure we always have new testimonials.
    if (availableTestimonialsPool.length === 0) {
        availableTestimonialsPool = allTestimonials.filter(t => 
            !currentTestimonials.some(ct => ct.name === t.name)
        );
    }
    
    // Pick a new random testimonial from the available pool.
    const newTestimonial = availableTestimonialsPool.shift(); // Get and remove from pool.

    // Fade out the old card.
    cardElementToReplace.classList.add('fade-out');

    // After the fade-out animation completes, update the content and fade it back in.
    setTimeout(() => {
        const oldTestimonial = currentTestimonials[cardToReplaceIndex];
        availableTestimonialsPool.push(oldTestimonial); // Add the old one back to the pool.
        currentTestimonials[cardToReplaceIndex] = newTestimonial;
        
        updateCardContent(cardElementToReplace, newTestimonial);
    }, 500); // This duration must match the CSS fade-out transition time.

    startAutoChange(); // Restart the timer.
}

if (testimonialPrevBtn) {
    testimonialPrevBtn.addEventListener('click', () => updateSingleTestimonial(-1));
}
if (testimonialNextBtn) {
    const nextBtn = document.getElementById(testimonialNextBtn);
    if(nextBtn) {
        nextBtn.addEventListener('click', () => updateSingleTestimonial(1));
    }
}


// ---------------------------------------------------------------------------------
// PAGE INITIALIZATION & SCROLL ANIMATIONS
// ---------------------------------------------------------------------------------

document.addEventListener('DOMContentLoaded', () => {
    // --- Initialize components ---
    if (document.getElementById('testimonial-grid')) {
        initializeTestimonials();
        startAutoChange();
    }
    
    // Set the minimum selectable date in the booking form to today.
    const today = new Date().toISOString().split('T')[0];
    const bookingDateEl = document.getElementById('booking-date');
    if (bookingDateEl) {
        bookingDateEl.setAttribute('min', today);
    }

    // Attach a listener to the "Add Another Booking" button
    const editBtn = document.getElementById('edit-booking-btn');
    if(editBtn) {
        editBtn.addEventListener('click', () => {
            // Hide the success message and show the form again.
            document.getElementById('success-message').classList.add('hidden');
            const formElement = document.getElementById('booking-form');
            formElement.style.display = 'block';

            // Clear only trip-specific fields to save user typing
            const tourSelect = document.getElementById('tour-select');
            if (tourSelect) tourSelect.value = '';
            
            const reqDate = document.getElementById('booking-date');
            if (reqDate) reqDate.value = '';
            
            const reqInput = document.getElementById('requests');
            if (reqInput) reqInput.value = '';
            
            const guestsInput = document.getElementById('guests');
            if (guestsInput) guestsInput.value = '1';

            formElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });
    }

    // --- Setup scroll animations ---
    const animatedElements = document.querySelectorAll('.animated-section, .animated-item');

    const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.01, // Lower threshold for large sections on mobile
        rootMargin: '0px'
    });

    animatedElements.forEach(el => {
        observer.observe(el);
    });

    // --- Tour Carousel Scroll Logic ---
    const toursCarousel = document.getElementById('tours-carousel');
    const scrollLeftBtn = document.getElementById('tours-scroll-left');
    const scrollRightBtn = document.getElementById('tours-scroll-right');

    if (toursCarousel && scrollLeftBtn && scrollRightBtn) {
        scrollLeftBtn.addEventListener('click', () => {
            toursCarousel.scrollBy({ left: -400, behavior: 'smooth' });
        });
        scrollRightBtn.addEventListener('click', () => {
            toursCarousel.scrollBy({ left: 400, behavior: 'smooth' });
        });
    }
    // --- WhatsApp Visibility Control (Hide on Hero and Footer) ---
    const whatsappBtn = document.querySelector('.whatsapp-brand-pill');
    const heroSection = document.getElementById('home');
    const footerContact = document.getElementById('contact');

    if (whatsappBtn && heroSection && footerContact) {
        const handleWhatsAppVisibility = () => {
            const scrolledFromTop = window.scrollY || document.documentElement.scrollTop;
            // Check if we are near the bottom of the page
            const isAtBottom = (window.innerHeight + window.scrollY) >= (document.body.offsetHeight - 500);

            // Hide if at the very top (Hero) OR near the footer
            if (scrolledFromTop < 300 || isAtBottom) {
                whatsappBtn.classList.add('whatsapp-hidden');
            } else {
                whatsappBtn.classList.remove('whatsapp-hidden');
            }
        };

        window.addEventListener('scroll', handleWhatsAppVisibility);
        // Initial check
        handleWhatsAppVisibility();
    }

    // --- Parallax Effect ---
    initParallax();
    initBackgroundParallax();
});

function initBackgroundParallax() {
    const parallaxItems = document.querySelectorAll('.parallax-bg');
    
    window.addEventListener('scroll', () => {
        const scrolled = window.scrollY;
        parallaxItems.forEach(item => {
            const parent = item.parentElement;
            const parentOffsetTop = parent.offsetTop;
            const parentHeight = parent.offsetHeight;
            const windowHeight = window.innerHeight;
            
            // Only animate if section is in or near viewport
            if (scrolled + windowHeight > parentOffsetTop && scrolled < parentOffsetTop + parentHeight) {
                const relativeScroll = scrolled - parentOffsetTop;
                const yPos = relativeScroll * 0.15; // Smooth parallax factor
                item.style.transform = `translate3d(0, ${yPos}px, 0)`;
            }
        });
    }, { passive: true });
}

function initParallax() {
    const heroImg = document.getElementById('hero-img');
    const aboutImg1 = document.getElementById('about-parallax-1');
    const aboutImg2 = document.getElementById('about-parallax-2');

    // Only proceed if at least one target exists and it's not a touch device
    if ((!heroImg && !aboutImg1 && !aboutImg2) || window.matchMedia("(pointer: coarse)").matches) return;

    window.addEventListener('mousemove', (e) => {
        // Calculate normalized mouse position (-0.5 to 0.5)
        const x = (e.clientX / window.innerWidth) - 0.5;
        const y = (e.clientY / window.innerHeight) - 0.5;

        // Apply hero parallax (subtle shift + 3D tilt)
        if (heroImg) {
            const rotateX = y * -10; // Tilt vertically
            const rotateY = x * 10;  // Tilt horizontally
            heroImg.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translate(${x * 40}px, ${y * 40}px) scale(1.1)`;
            heroImg.style.transition = 'transform 0.4s cubic-bezier(0.23, 1, 0.32, 1)';
        }

        // Apply about heritage parallax (opposing shifts + stronger tilt for depth)
        if (aboutImg1) {
            const rotateX = y * 8;
            const rotateY = x * -8;
            aboutImg1.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translate(${x * -35}px, ${y * -35}px) scale(1.05)`;
            aboutImg1.style.transition = 'transform 0.4s cubic-bezier(0.23, 1, 0.32, 1)';
        }
        if (aboutImg2) {
            const rotateX = y * -15; // Stronger tilt for the floating image
            const rotateY = x * 15;
            aboutImg2.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translate(${x * 45}px, ${y * 45}px) scale(1.15)`;
            aboutImg2.style.transition = 'transform 0.4s cubic-bezier(0.23, 1, 0.32, 1)';
        }
    });

    // Reset when mouse leaves window for better transition
    window.addEventListener('mouseout', (e) => {
        if (!e.relatedTarget && !e.toElement) {
            const items = [heroImg, aboutImg1, aboutImg2];
            items.forEach(item => {
                if (item) {
                   item.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) translate(0px, 0px) scale(1)';
                   item.style.transition = 'transform 1s cubic-bezier(0.23, 1, 0.32, 1)';
                }
            });
        }
    });
}
