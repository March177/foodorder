document.addEventListener("DOMContentLoaded", function () {
  const idFrontImageInput = document.getElementById("id_front_image");
  const idBackImageInput = document.getElementById("id_back_image");
  const idFrontPreview = document.getElementById("id-front-preview");
  const idBackPreview = document.getElementById("id-back-preview");
  const idFrontImage = document.getElementById("id-front-image");
  const idBackImage = document.getElementById("id-back-image");

  function updateImagePreview(input, previewElement, imgElement) {
    const file = input.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = function (e) {
        imgElement.src = e.target.result;
        previewElement.classList.remove("hidden");
      };
      reader.readAsDataURL(file);
    } else {
      previewElement.classList.add("hidden");
    }
  }

  idFrontImageInput.addEventListener("change", function () {
    updateImagePreview(idFrontImageInput, idFrontPreview, idFrontImage);
  });

  idBackImageInput.addEventListener("change", function () {
    updateImagePreview(idBackImageInput, idBackPreview, idBackImage);
  });
});
