const map = L.map("map").setView([10.2098, 123.758], 13);

L.tileLayer("https://tile.openstreetmap.org/{z}/{x}/{y}.png", {
  maxZoom: 19,
  attribution: "© OpenStreetMap",
}).addTo(map);

let marker, circle, zoomed;

// Initialize geocoder control
const geocoder = L.Control.geocoder({
  defaultMarkGeocode: false,
})
  .on("markgeocode", function (e) {
    const latlng = e.geocode.center;
    if (marker) map.removeLayer(marker);
    marker = L.marker(latlng, {
      draggable: true,
    })
      .addTo(map)
      .bindPopup(e.geocode.name)
      .openPopup();
    map.setView(latlng, 15);
    updateAddress(latlng.lat, latlng.lng);
    updateHiddenLocationFields(latlng.lat, latlng.lng);

    // Add event listener for marker drag
    marker.on("dragend", function (event) {
      const latlng = event.target.getLatLng();
      updateAddress(latlng.lat, latlng.lng);
      updateHiddenLocationFields(latlng.lat, latlng.lng);
    });
  })
  .addTo(map);

// Function to fetch autocomplete suggestions
function fetchSuggestions(query) {
  const url = `https://nominatim.openstreetmap.org/search?format=json&q=${query}&limit=5`;
  return fetch(url)
    .then((response) => response.json())
    .then((data) =>
      data.map((result) => ({
        display_name: result.display_name,
        lat: result.lat,
        lon: result.lon,
      }))
    );
}

// Function to show suggestions
function showSuggestions(suggestions) {
  const suggestionsContainer = document.getElementById(
    "autocomplete-suggestions"
  );
  suggestionsContainer.innerHTML = "";

  suggestions.forEach((suggestion) => {
    const div = document.createElement("div");
    div.className = "autocomplete-suggestion";
    div.textContent = suggestion.display_name;
    div.addEventListener("click", () => {
      selectSuggestion(suggestion);
    });
    suggestionsContainer.appendChild(div);
  });

  suggestionsContainer.style.display = suggestions.length ? "block" : "none";
}

// Handle suggestion selection
function selectSuggestion(suggestion) {
  const latlng = L.latLng(suggestion.lat, suggestion.lon);
  if (marker) {
    marker.setLatLng(latlng);
  } else {
    marker = L.marker(latlng, {
      draggable: true,
    }).addTo(map);
  }
  map.setView(latlng, 15);
  updateAddress(suggestion.lat, suggestion.lon);
  updateHiddenLocationFields(suggestion.lat, suggestion.lon);

  // Hide suggestions after selection
  document.getElementById("autocomplete-suggestions").style.display = "none";
}

// Update address information
function updateAddress(lat, lng) {
  const url = `https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json&addressdetails=1`;
  fetch(url)
    .then((response) => response.json())
    .then((data) => {
      const address = data.display_name || "No address found";
      document.getElementById(
        "location-info"
      ).innerHTML = `Location: ${address}`;
    })
    .catch((error) => {
      console.error("Error fetching address:", error);
      document.getElementById("location-info").innerHTML =
        "Unable to retrieve address";
    });
}

document.getElementById("search-box").addEventListener("input", function () {
  const query = this.value;
  if (query.length >= 2) {
    // Trigger search for input longer than 2 characters
    fetchSuggestions(query).then((suggestions) => {
      showSuggestions(suggestions);
    });
  } else {
    showSuggestions([]); // Hide suggestions if query is too short
  }
});

// Hide suggestions container when clicking outside
document.addEventListener("click", function (e) {
  if (
    !document.getElementById("autocomplete-suggestions").contains(e.target) &&
    !document.getElementById("search-box").contains(e.target)
  ) {
    document.getElementById("autocomplete-suggestions").style.display = "none";
  }
});

document
  .getElementById("add-edit-location")
  .addEventListener("click", function () {
    const floor = document.querySelector('input[name="floor"]').value;
    const note = document.querySelector('textarea[name="note_to_rider"]').value;
    alert(`Location added: Floor: ${floor}, Note: ${note}`);
    updateHiddenFields(floor, note);

    // Hide the "Add this as Address" button and show the "Edit Address" button
    document.getElementById("add-edit-location").classList.add("hidden");
    document.getElementById("edit-address").classList.remove("hidden");
  });

document.getElementById("edit-address").addEventListener("click", function () {
  // Show the "Add this as Address" button and hide the "Edit Address" button
  document.getElementById("add-edit-location").classList.remove("hidden");
  document.getElementById("edit-address").classList.add("hidden");
});

function updateHiddenFields(floor, note) {
  document.getElementById("floor-hidden").value = floor;
  document.getElementById("note-to-rider-hidden").value = note;
}

function updateHiddenLocationFields(lat, lng) {
  document.getElementById("latitude-hidden").value = lat;
  document.getElementById("longitude-hidden").value = lng;
}
