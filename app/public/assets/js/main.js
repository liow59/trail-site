// Race selection
let selectedRace = null;
let selectedPrice = 0;

const racePrices = {
  '3km': 0,
  '7.5km': 10,
  '15km': 15
};

const mealPrices = {
  'poulet': 10,
  'saucisse': 12,
  'nuggets': 8
};

// Pré-sélection depuis URL
const urlParams = new URLSearchParams(window.location.search);
const preselectedCourse = urlParams.get('course');

// Race card selection
document.querySelectorAll('.race-card').forEach(card => {
  const race = card.dataset.race;
  
  // Pré-sélectionner si course dans l'URL
  if (preselectedCourse === race) {
    setTimeout(() => {
      card.click();
    }, 100);
  }
  
  card.addEventListener('click', () => {
    const price = parseInt(card.dataset.price);

    // Toggle selection
    document.querySelectorAll('.race-card').forEach(c => c.classList.remove('selected'));
    card.classList.add('selected');

    selectedRace = race;
    selectedPrice = price;

    // Update hidden input
    const hiddenInput = document.getElementById('selected-course');
    if (hiddenInput) {
      hiddenInput.value = race;
    }

    // Update price display
    updatePrices();

    // Enable submit if form is valid
    checkFormValidity();
  });
});

// Meal quantity buttons
document.querySelectorAll('.qty-btn').forEach(btn => {
  btn.addEventListener('click', (e) => {
    e.preventDefault();
    e.stopPropagation();
    
    const meal = btn.dataset.meal;
    const input = document.querySelector(`input[name="repas_${meal}"]`);
    
    if (!input) return;
    
    let value = parseInt(input.value) || 0;

    if (btn.classList.contains('qty-plus')) {
      value++;
    } else if (btn.classList.contains('qty-minus') && value > 0) {
      value--;
    }

    input.value = value;
    updatePrices();
  });
});

function updatePrices() {
  // Course price
  const coursePrice = selectedPrice || 0;
  const coursePriceEl = document.getElementById('course-price');
  if (coursePriceEl) {
    coursePriceEl.textContent = coursePrice + ' €';
  }

  // Meal total
  let mealTotal = 0;
  Object.keys(mealPrices).forEach(meal => {
    const input = document.querySelector(`input[name="repas_${meal}"]`);
    if (input) {
      const qty = parseInt(input.value) || 0;
      mealTotal += qty * mealPrices[meal];
    }
  });
  
  const mealPriceEl = document.getElementById('meal-price');
  if (mealPriceEl) {
    mealPriceEl.textContent = mealTotal + ' €';
  }

  // Total
  const total = coursePrice + mealTotal;
  const totalPriceEl = document.getElementById('total-price');
  if (totalPriceEl) {
    totalPriceEl.textContent = total + ' €';
  }
}

function checkFormValidity() {
  const submitBtn = document.querySelector('.submit-btn');
  if (!submitBtn) return;
  
  const prenom = document.querySelector('input[name="prenom"]');
  const nom = document.querySelector('input[name="nom"]');
  const email = document.querySelector('input[name="email"]');
  const telephone = document.querySelector('input[name="telephone"]');
  const dateNaissance = document.querySelector('input[name="date_naissance"]');
  const sexe = document.querySelector('select[name="sexe"]');

  const allFilled = selectedRace && 
                    prenom && prenom.value.trim() && 
                    nom && nom.value.trim() && 
                    email && email.value.trim() && 
                    telephone && telephone.value.trim() && 
                    dateNaissance && dateNaissance.value && 
                    sexe && sexe.value;

  submitBtn.disabled = !allFilled;
}

// Listen to all inputs for validation
document.querySelectorAll('input, select').forEach(el => {
  el.addEventListener('input', checkFormValidity);
  el.addEventListener('change', checkFormValidity);
});

// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', function (e) {
    e.preventDefault();
    const target = document.querySelector(this.getAttribute('href'));
    if (target) {
      target.scrollIntoView({ behavior: 'smooth' });
    }
  });
});

// Initial state
updatePrices();
checkFormValidity();
