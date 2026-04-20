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
    card.classList.add('selected');
    selectedRace = race;
    selectedPrice = parseInt(card.dataset.price);
    document.getElementById('selected-course').value = race;
    updatePrices();
    checkFormValidity();
  }
  
  card.addEventListener('click', () => {
    const price = parseInt(card.dataset.price);

    // Toggle selection
    document.querySelectorAll('.race-card').forEach(c => c.classList.remove('selected'));
    card.classList.add('selected');

    selectedRace = race;
    selectedPrice = price;

    // Update hidden input
    document.getElementById('selected-course').value = race;

    // Update price display
    updatePrices();

    // Enable submit if form is valid
    checkFormValidity();
  });
});

// Meal quantity buttons
document.querySelectorAll('.qty-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const meal = btn.dataset.meal;
    const input = btn.parentElement.querySelector('.qty-input');
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
  document.getElementById('course-price').textContent = coursePrice + ' €';

  // Meal total
  let mealTotal = 0;
  Object.keys(mealPrices).forEach(meal => {
    const qty = parseInt(document.querySelector(`input[name="repas_${meal}"]`).value) || 0;
    mealTotal += qty * mealPrices[meal];
  });
  document.getElementById('meal-price').textContent = mealTotal + ' €';

  // Total
  const total = coursePrice + mealTotal;
  document.getElementById('total-price').textContent = total + ' €';
}

function checkFormValidity() {
  const prenom = document.querySelector('input[name="prenom"]').value.trim();
  const nom = document.querySelector('input[name="nom"]').value.trim();
  const email = document.querySelector('input[name="email"]').value.trim();
  const telephone = document.querySelector('input[name="telephone"]').value.trim();
  const dateNaissance = document.querySelector('input[name="date_naissance"]').value;
  const sexe = document.querySelector('select[name="sexe"]').value;

  const allFilled = selectedRace && prenom && nom && email && telephone && dateNaissance && sexe;

  document.querySelector('.submit-btn').disabled = !allFilled;
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
