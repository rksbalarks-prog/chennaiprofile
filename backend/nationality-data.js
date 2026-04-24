// ===== NATIONALITY: Country list (ISO 3166-1) =====
// Shared by user-panel.php and admin-panel.php
// India first, then alphabetical

const NATIONALITIES = [
  "Indian",
  "Afghan", "Albanian", "Algerian", "American", "Andorran", "Angolan", "Argentine",
  "Armenian", "Australian", "Austrian", "Azerbaijani", "Bahamian", "Bahraini",
  "Bangladeshi", "Barbadian", "Belarusian", "Belgian", "Belizean", "Beninese",
  "Bhutanese", "Bolivian", "Bosnian", "Brazilian", "British", "Bruneian",
  "Bulgarian", "Burkinabe", "Burmese", "Burundian", "Cambodian", "Cameroonian",
  "Canadian", "Central African", "Chadian", "Chilean", "Chinese", "Colombian",
  "Congolese", "Costa Rican", "Croatian", "Cuban", "Cypriot", "Czech",
  "Danish", "Dominican", "Dutch", "Ecuadorian", "Egyptian", "Emirati",
  "Eritrean", "Estonian", "Ethiopian", "Fijian", "Filipino", "Finnish",
  "French", "Gabonese", "Gambian", "Georgian", "German", "Ghanaian",
  "Greek", "Guatemalan", "Guinean", "Guyanese", "Haitian", "Honduran",
  "Hungarian", "Icelandic", "Indonesian", "Iranian", "Iraqi", "Irish",
  "Israeli", "Italian", "Ivorian", "Jamaican", "Japanese", "Jordanian",
  "Kazakh", "Kenyan", "Korean", "Kuwaiti", "Kyrgyz", "Laotian",
  "Latvian", "Lebanese", "Liberian", "Libyan", "Lithuanian", "Luxembourgish",
  "Macedonian", "Malagasy", "Malawian", "Malaysian", "Maldivian", "Malian",
  "Maltese", "Mauritanian", "Mauritian", "Mexican", "Moldovan", "Mongolian",
  "Montenegrin", "Moroccan", "Mozambican", "Namibian", "Nepalese", "New Zealander",
  "Nicaraguan", "Nigerian", "Norwegian", "Omani", "Pakistani", "Palestinian",
  "Panamanian", "Paraguayan", "Peruvian", "Polish", "Portuguese", "Qatari",
  "Romanian", "Russian", "Rwandan", "Saudi", "Senegalese", "Serbian",
  "Sierra Leonean", "Singaporean", "Slovak", "Slovenian", "Somali", "South African",
  "South Korean", "Spanish", "Sri Lankan", "Sudanese", "Surinamese", "Swedish",
  "Swiss", "Syrian", "Taiwanese", "Tajik", "Tanzanian", "Thai",
  "Togolese", "Trinidadian", "Tunisian", "Turkish", "Turkmen", "Ugandan",
  "Ukrainian", "Uruguayan", "Uzbek", "Venezuelan", "Vietnamese", "Yemeni",
  "Zambian", "Zimbabwean"
];

// Populate a nationality select dropdown
function populateNationality(selectId, currentVal) {
  const el = document.getElementById(selectId);
  if (!el) return;
  const cur = currentVal || el.value;
  el.innerHTML = '<option value="">— Select —</option>';
  NATIONALITIES.forEach(n => {
    el.innerHTML += '<option value="' + n + '"' + (n === 'Indian' ? ' style="font-weight:700"' : '') + '>' + n + '</option>';
  });
  if (cur) {
    if (!NATIONALITIES.includes(cur)) {
      el.innerHTML += '<option value="' + cur + '">' + cur + '</option>';
    }
    el.value = cur;
  }
}

// Country names list (ISO 3166-1) — India first, then alphabetical
const COUNTRIES = [
  "India",
  "Afghanistan", "Albania", "Algeria", "Argentina", "Armenia", "Australia",
  "Austria", "Azerbaijan", "Bahrain", "Bangladesh", "Belgium", "Bhutan",
  "Bolivia", "Bosnia", "Brazil", "Brunei", "Bulgaria", "Cambodia", "Cameroon",
  "Canada", "Chile", "China", "Colombia", "Croatia", "Cuba", "Cyprus",
  "Czech Republic", "Denmark", "Dominican Republic", "Ecuador", "Egypt",
  "Estonia", "Ethiopia", "Fiji", "Finland", "France", "Georgia", "Germany",
  "Ghana", "Greece", "Guatemala", "Hungary", "Iceland", "Indonesia", "Iran",
  "Iraq", "Ireland", "Israel", "Italy", "Jamaica", "Japan", "Jordan",
  "Kazakhstan", "Kenya", "Kuwait", "Latvia", "Lebanon", "Libya", "Lithuania",
  "Luxembourg", "Malaysia", "Maldives", "Malta", "Mauritius", "Mexico",
  "Mongolia", "Morocco", "Mozambique", "Myanmar", "Nepal", "Netherlands",
  "New Zealand", "Nigeria", "Norway", "Oman", "Pakistan", "Palestine",
  "Panama", "Paraguay", "Peru", "Philippines", "Poland", "Portugal", "Qatar",
  "Romania", "Russia", "Saudi Arabia", "Serbia", "Singapore", "Slovakia",
  "Slovenia", "Somalia", "South Africa", "South Korea", "Spain", "Sri Lanka",
  "Sudan", "Sweden", "Switzerland", "Syria", "Taiwan", "Tanzania", "Thailand",
  "Tunisia", "Turkey", "UAE", "Uganda", "UK", "Ukraine", "Uruguay", "USA",
  "Uzbekistan", "Venezuela", "Vietnam", "Yemen", "Zambia", "Zimbabwe"
];

// Populate a country select dropdown
function populateCountry(selectId, currentVal) {
  const el = document.getElementById(selectId);
  if (!el) return;
  const cur = currentVal || el.value;
  el.innerHTML = '<option value="">— Select —</option>';
  COUNTRIES.forEach(c => {
    el.innerHTML += '<option value="' + c + '"' + (c === 'India' ? ' style="font-weight:700"' : '') + '>' + c + '</option>';
  });
  if (cur) {
    if (!COUNTRIES.includes(cur)) {
      el.innerHTML += '<option value="' + cur + '">' + cur + '</option>';
    }
    el.value = cur;
  }
}
