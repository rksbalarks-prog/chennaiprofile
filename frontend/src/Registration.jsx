// RegisterForm.jsx
import { useState, useEffect, useRef, useCallback } from "react";
import { useTranslation } from "react-i18next";
import { checkIfFaceExists } from "./utils/faceDetection";
import { generateFormPDF } from "./utils/generatePDF";
import { API_BASE, USER_PANEL_URL } from "./config";

// ─── Helper Components (defined outside to prevent recreation on re-render) ───
const SectionHeader = ({ icon, title }) => (
  <div style={{ display: "flex", alignItems: "center", gap: 12, marginBottom: 24, paddingBottom: 14, borderBottom: "1px solid rgba(139,0,0,0.12)" }}>
    <div style={{ width: 38, height: 38, borderRadius: "50%", background: "linear-gradient(135deg,#8B0000,#C41E3A)", display: "flex", alignItems: "center", justifyContent: "center", fontSize: 16, flexShrink: 0, boxShadow: "0 4px 12px rgba(139,0,0,0.25)" }}>{icon}</div>
    <h2 style={{ margin: 0, fontSize: "1.15rem", fontFamily: "'Playfair Display',serif", fontWeight: 700, color: "#5A0010", letterSpacing: "0.03em" }}>{title}</h2>
    <div style={{ flex: 1, height: 1, background: "linear-gradient(90deg,rgba(139,0,0,0.2),transparent)" }} />
  </div>
);

const FormField = ({ label, required, error, children }) => (
  <div style={{ display: "flex", flexDirection: "column", gap: 6, width: "100%" }}>
    <label style={{ fontSize: "0.72rem", fontWeight: 700, color: "#7A1020", textTransform: "uppercase", letterSpacing: "0.08em", display: "flex", alignItems: "center", gap: 4 }}>
      {required && <span style={{ color: "#C41E3A" }}>✦</span>}{label}
    </label>
    {children}
    {error && <span style={{ fontSize: "0.72rem", color: "#C41E3A", fontStyle: "italic" }}>{error}</span>}
  </div>
);

const RadioOpt = ({ name, value, checked, onChange, label }) => (
  <label style={{ display: "flex", alignItems: "center", gap: 8, cursor: "pointer", fontSize: "0.875rem", color: "#3D0010", fontFamily: "'Lato',sans-serif" }}>
    <div style={{ position: "relative", width: 18, height: 18 }}>
      <input type="radio" name={name} value={value} checked={checked} onChange={onChange} style={{ position: "absolute", opacity: 0, width: "100%", height: "100%", cursor: "pointer", margin: 0 }} />
      <div style={{ width: 18, height: 18, border: `2px solid ${checked ? "#8B0000" : "#C4A0A8"}`, borderRadius: "50%", background: checked ? "#8B0000" : "white", transition: "all 0.2s", display: "flex", alignItems: "center", justifyContent: "center" }}>
        {checked && <div style={{ width: 6, height: 6, background: "white", borderRadius: "50%" }} />}
      </div>
    </div>
    {label}
  </label>
);

const CustomSelect = ({ id, value, onChange, options, hasErr, compact, openDropdownId, setOpenDropdownId }) => {
  const isOpen = openDropdownId === id;

  const toggle = (e) => {
    e.stopPropagation();
    setOpenDropdownId(isOpen ? null : id);
  };

  const select = (opt, e) => {
    e.stopPropagation();
    onChange(opt);
    setOpenDropdownId(null);
  };

  return (
    <div data-custom-select style={{ position: "relative", width: "100%" }}>
      {/* Trigger */}
      <div
        onClick={toggle}
        style={{
          padding: compact ? "7px 10px" : "10px 14px",
          border: `1.5px solid ${hasErr ? "#C41E3A" : isOpen ? "#8B0000" : "#D4A0A8"}`,
          borderRadius: 8,
          fontSize: compact ? "0.82rem" : "0.88rem",
          fontFamily: "'Lato',sans-serif",
          background: "#FFFAF9",
          color: "#2A0A0E",
          cursor: "pointer",
          display: "flex",
          justifyContent: "space-between",
          alignItems: "center",
          boxShadow: hasErr
            ? "0 0 0 3px rgba(196,30,58,0.1)"
            : isOpen
            ? "0 0 0 3px rgba(139,0,0,0.12)"
            : "none",
          transition: "all 0.2s ease",
          userSelect: "none",
          minWidth: compact ? 60 : undefined,
        }}
      >
        <span style={{ overflow: "hidden", textOverflow: "ellipsis", whiteSpace: "nowrap" }}>
          {value || "-Select-"}
        </span>
        <span
          style={{
            fontSize: 11,
            color: "#8B0000",
            marginLeft: 6,
            flexShrink: 0,
            transform: isOpen ? "rotate(180deg)" : "rotate(0deg)",
            transition: "transform 0.2s ease",
            display: "inline-block",
          }}
        >
          ▼
        </span>
      </div>

      {/* Dropdown list */}
      {isOpen && (
        <div
          style={{
            position: "absolute",
            top: "calc(100% + 4px)",
            left: 0,
            right: 0,
            background: "#fff",
            border: "1.5px solid #D4A0A8",
            borderRadius: 8,
            maxHeight: 240,
            overflowY: "auto",
            zIndex: 9999,
            boxShadow: "0 8px 24px rgba(139,0,0,0.15)",
            minWidth: compact ? 80 : undefined,
          }}
        >
          {options.map((opt) => {
            const isSelected = value === opt;
            return (
              <div
                key={opt}
                onClick={(e) => select(opt, e)}
                style={{
                  padding: compact ? "8px 10px" : "10px 14px",
                  cursor: "pointer",
                  background: isSelected ? "#FFF0F2" : "white",
                  color: isSelected ? "#8B0000" : "#2A0A0E",
                  fontWeight: isSelected ? 700 : 400,
                  fontSize: compact ? "0.82rem" : "0.88rem",
                  fontFamily: "'Lato',sans-serif",
                  borderBottom: "1px solid rgba(139,0,0,0.07)",
                  transition: "background 0.12s, color 0.12s",
                  whiteSpace: "nowrap",
                }}
                onMouseEnter={(e) => {
                  if (!isSelected) {
                    e.currentTarget.style.background = "#FFF0F2";
                    e.currentTarget.style.color = "#8B0000";
                  }
                }}
                onMouseLeave={(e) => {
                  if (!isSelected) {
                    e.currentTarget.style.background = "white";
                    e.currentTarget.style.color = "#2A0A0E";
                  }
                }}
              >
                {opt}
              </div>
            );
          })}
        </div>
      )}
    </div>
  );
};

export default function PersonalFamilyForm() {
  const { t, i18n } = useTranslation();

  // Helper function to build dropdown lists from translation keys
  const getTranslationList = (keyMapping) => {
    return [t("registration.genderSelect"), ...keyMapping.map(key => t(`registration.rasiList.${key}`))];
  };

  const getStarList = (keyMapping) => {
    return [t("registration.genderSelect"), ...keyMapping.map(key => t(`registration.nakshatraList.${key}`))];
  };

  const getLagnamList = (keyMapping) => {
    return [t("registration.genderSelect"), ...keyMapping.map(key => t(`registration.lagnamList.${key}`))];
  };

  const getDoshamList = (keyMapping) => {
    return [t("registration.genderSelect"), ...keyMapping.map(key => t(`registration.doshamList.${key}`))];
  };

  const GENDERS = [t("registration.genderSelect"), t("registration.genderMale"), t("registration.genderFemale")];
  const HOURS = Array.from({ length: 24 }, (_, i) => String(i).padStart(2, "0"));
  const MINUTES = ["00", "15", "30", "45"];
  const AMPM = ["AM", "PM"];
  const MOTHER_TONGUES = [t("common.select"), "Tamil", "Telugu", "Kannada", "Malayalam", "Hindi", "Marathi", "English"];
  const MARITAL_STATUSES = [t("registration.unmarried"), t("registration.married"), t("registration.divorced"), t("registration.widowed")];
  const SIBLING_COUNTS = ["-", "0", "1", "2", "3", "4", "5"];
  const HEIGHTS = [t("registration.genderSelect"), "4'8\"", "4'9\"", "4'10\"", "4'11\"", "5'0\"", "5'1\"", "5'2\"", "5'3\"", "5'4\"", "5'5\"", "5'6\"", "5'7\"", "5'8\"", "5'9\"", "5'10\"", "5'11\"", "6'0\""];
  const WEIGHTS = [t("registration.genderSelect"), "40kg", "45kg", "50kg", "55kg", "60kg", "65kg", "70kg", "75kg", "80kg", "85kg", "90kg"];
  const BLOOD_GROUPS = [t("registration.genderSelect"), "O+", "O-", "A+", "A-", "B+", "B-", "AB+", "AB-"];
  
  // Get caste options from translations
  const casteOptionsRaw = t("registration.casteOptions", { returnObjects: true }) || [];
  const CASTES = casteOptionsRaw.map(o => o.label);
  
  const SUB_CASTES = [t("registration.selectSubCaste"), "Subgroup 1", "Subgroup 2", "Subgroup 3"];
  
  // Rasi keys in order
  const rasiKeys = ["mesham", "rishabam", "mithunam", "kadagam", "simmam", "kanni", "thulam", "viruchigam", "dhanusu", "magaram", "kumbam", "meenam"];
  const RASI_LIST = getTranslationList(rasiKeys);
  
  // Star/Nakshatra keys in order
  const starKeys = ["ashwini", "bharani", "krittika", "rohini", "mrigashirsha", "ardra", "punarvasu", "pushya", "ashlesha", "magha", "purva_phalguni", "uttara_phalguni", "hasta", "chitra", "swati", "vishakha", "anuradha", "jyeshtha", "moola", "purva_ashadha", "uttara_ashadha", "shravana", "dhanishta", "shatabhisha", "purva_bhadrapada", "uttara_bhadrapada", "revati"];
  const STARS = getStarList(starKeys);
  
  // Lagnam keys in order
  const lagnamKeys = ["mesha", "rishaba", "mithuna", "kataka", "simha", "kanya", "tula", "vrischika", "dhanu", "makara", "kumbha", "meena"];
  const LAKNAM = getLagnamList(lagnamKeys);
  
  // Dosham keys in order
  const doshamKeys = ["no_dosham", "mangal_dosham", "rahu_ketu_dosham"];
  const DOSHAM_LIST = getDoshamList(doshamKeys);
  
  const PADAM_LIST = [t("registration.genderSelect"), "Padam 1", "Padam 2", "Padam 3", "Padam 4"];
  const DIET_OPTIONS = [t("registration.dietVegetarian"), t("registration.dietNonVegetarian"), t("registration.dietEggetarian"), t("registration.dietNoPreference")];
  const PARTNER_CASTE_OPTIONS = [t("registration.casteAny"), t("registration.casteSame"), t("registration.casteOthers")];
  const PARTNER_SUB_CASTE_OPTIONS = [t("registration.casteAny"), t("registration.subCasteSame"), t("registration.casteOthers")];
  const PARTNER_MARITAL_OPTIONS = [t("registration.unmarried"), t("registration.divorced"), t("registration.widowed"), t("registration.separated"), t("registration.casteAny")];
  const JOB_REQUIREMENT_OPTIONS = [t("registration.mustRequired"), t("registration.optional"), t("registration.notRequired")];

  const INITIAL_FORM = {
    name: "", gender: "-Select-", dob: "", birthHour: "", birthMin: "", birthAmPm: "AM",
    placeBirth: "", nativity: "", motherTongue: "Select", maritalStatus: "Unmarried",
    fatherName: "", fatherAlive: "yes", fatherJob: "",
    motherName: "", motherAlive: "yes", motherJob: "",
    sibMarriedEB: "", sibMarriedYB: "", sibMarriedES: "", sibMarriedYS: "",
    sibUnmarriedEB: "", sibUnmarriedYB: "", sibUnmarriedES: "", sibUnmarriedYS: "",
    others: "",
    height: "-Select-", weight: "-Select-", bloodGroup: "-Select-",
    diet: "Vegetarian", disability: "No", complexion: "Very Fair",
    qualification: "", job: "", placeJob: "", incomeMonth: "",
    partnerQualification: "", partnerJob: "", partnerJobRequirement: "Optional",
    partnerIncomeMonth: "", partnerAgeFrom: "", partnerAgeTo: "",
    partnerDiet: "Vegetarian", partnerHoroscopeRequired: "No",
    partnerMaritalStatus: "Unmarried", partnerCaste: "Any", partnerSubCaste: "Any",
    partnerOtherRequirement: "",
    caste: "-Select-", subCaste: "-select-", gothram: "",
    star: "-Select-", raasi: "-Select Rasi-", padam: "-Select Padam-",
    laknam: "-Select Laknam-", dosham: "-Select-",
    permanentAddress: "", presentAddress: "", contactPerson: "", contactNumber: "",
    scheme: "Select", username: "", password: "", termsAccepted: false,
  };

  const [form, setForm] = useState(INITIAL_FORM);
  const [photos, setPhotos] = useState([null, null, null]);
  const [photoPreviews, setPhotoPreviews] = useState([null, null, null]);
  const [photoValidating, setPhotoValidating] = useState(false);
  const [photoProgress, setPhotoProgress] = useState(0);
  const [horoscopePhotos, setHoroscopePhotos] = useState({ rasi: null, amsam: null });
  const [horoscopePreview, setHoroscopePreview] = useState({ rasi: null, amsam: null });
  const [errors, setErrors] = useState({});
  const [submitted, setSubmitted] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [downloadingPDF, setDownloadingPDF] = useState(false);
  const [pdfLanguage, setPdfLanguage] = useState("en");

  // Global open dropdown tracker — only one open at a time
  const [openDropdownId, setOpenDropdownId] = useState(null);

  const handleDownloadPDF = async () => {
    setDownloadingPDF(true);
    try {
      await generateFormPDF(pdfLanguage);
    } catch (err) {
      console.error("Error downloading PDF:", err);
      alert("Error downloading PDF. Please try again.");
    } finally {
      setDownloadingPDF(false);
    }
  };

  // Close all dropdowns when clicking outside
  useEffect(() => {
    const handleClickOutside = (e) => {
      if (!e.target.closest("[data-custom-select]")) {
        setOpenDropdownId(null);
      }
    };
    document.addEventListener("mousedown", handleClickOutside);
    return () => document.removeEventListener("mousedown", handleClickOutside);
  }, []);

  useEffect(() => {}, [i18n.language]);

  const set = (k, v) => {
    setForm(f => ({ ...f, [k]: v }));
    if (errors[k]) setErrors(e => ({ ...e, [k]: "" }));
  };

  const validateHumanPhoto = async (file) => {
    return await checkIfFaceExists(file);
  };

  const handlePhotoUpload = async (files) => {
    try {
      if (!files || files.length === 0) return;
      const currentCount = photos.filter(Boolean).length;
      const remaining = 3 - currentCount;
      if (remaining === 0) {
        alert("You have already uploaded 3 photos. Remove one to add more.");
        return;
      }
      const filesToProcess = Array.from(files).slice(0, remaining);
      setPhotoValidating(true);
      setPhotoProgress(0);
      const newPhotos = [...photos];
      const newPreviews = [...photoPreviews];
      const total = filesToProcess.length;
      let done = 0;
      for (const file of filesToProcess) {
        try {
          if (file.size > 5 * 1024 * 1024) {
            alert(`"${file.name}" exceeds 5MB. Please choose a smaller image.`);
            done++; setPhotoProgress(Math.round((done / total) * 100)); continue;
          }
          if (!file.type.startsWith("image/")) {
            alert(`"${file.name}" is not a valid image file.`);
            done++; setPhotoProgress(Math.round((done / total) * 100)); continue;
          }
          const isHuman = await validateHumanPhoto(file);
          if (!isHuman) {
            alert("Please upload your photo correctly");
            done++; setPhotoProgress(Math.round((done / total) * 100)); continue;
          }
          const slotIndex = newPhotos.findIndex(p => p === null);
          if (slotIndex === -1) break;
          await new Promise((resolve) => {
            const reader = new FileReader();
            reader.onload = (e) => {
              newPhotos[slotIndex] = file;
              newPreviews[slotIndex] = e.target.result;
              done++; setPhotoProgress(Math.round((done / total) * 100)); resolve();
            };
            reader.onerror = () => { done++; setPhotoProgress(Math.round((done / total) * 100)); resolve(); };
            reader.readAsDataURL(file);
          });
        } catch (err) {
          console.error(`Error processing file ${file.name}:`, err);
          done++; setPhotoProgress(Math.round((done / total) * 100));
        }
      }
      setPhotos(newPhotos);
      setPhotoPreviews(newPreviews);
    } catch (err) {
      console.error("Upload error:", err);
      alert("An error occurred during upload. Please try again.");
    } finally {
      setPhotoValidating(false);
      setPhotoProgress(0);
    }
  };

  const removePhoto = (index) => {
    const np = [...photos]; const nv = [...photoPreviews];
    np[index] = null; nv[index] = null;
    setPhotos(np); setPhotoPreviews(nv);
  };

  const handleHoroscopePhotoUpload = (type, file) => {
    if (!file) return;
    if (file.size > 5 * 1024 * 1024) { alert("Image size must be less than 5MB"); return; }
    if (!file.type.startsWith("image/")) { alert("Please select a valid image file"); return; }
    const reader = new FileReader();
    reader.onload = (e) => {
      setHoroscopePhotos(p => ({ ...p, [type]: file }));
      setHoroscopePreview(p => ({ ...p, [type]: e.target.result }));
    };
    reader.readAsDataURL(file);
  };

  const removeHoroscopePhoto = (type) => {
    setHoroscopePhotos(p => ({ ...p, [type]: null }));
    setHoroscopePreview(p => ({ ...p, [type]: null }));
  };

  const validate = () => {
    const e = {};
    if (!form.name.trim()) e.name = t("registration.validationNameRequired");
    if (form.gender === t("registration.genderSelect")) e.gender = t("registration.validationGenderRequired");
    if (!form.dob) e.dob = t("registration.validationDobRequired");
    if (form.motherTongue === t("common.select")) e.motherTongue = t("registration.validationMotherTongueRequired");
    if (!form.maritalStatus.trim()) e.maritalStatus = t("registration.validationMaritalStatusRequired");
    if (!form.placeBirth.trim()) e.placeBirth = t("registration.validationPlaceBirthRequired");
    if (!form.nativity.trim()) e.nativity = t("registration.validationNativityRequired");
    if (!form.contactNumber.trim()) e.contactNumber = t("registration.validationContactRequired");
    if (form.contactNumber && !/^\d{10}$/.test(form.contactNumber)) e.contactNumber = t("registration.validationPhoneInvalid");
    return e;
  };

  const handleSubmit = async () => {
    const errs = validate();
    setErrors(errs);
    if (Object.keys(errs).length > 0) return;
    setSubmitting(true);
    try {
      const formData = new FormData();
      Object.keys(form).forEach(k => formData.append(k, form[k]));
      photos.forEach((p, i) => { if (p) formData.append(`photo${i + 1}`, p); });
      if (horoscopePhotos.rasi) formData.append("rasiPhoto", horoscopePhotos.rasi);
      if (horoscopePhotos.amsam) formData.append("amsamPhoto", horoscopePhotos.amsam);
      const res = await fetch(API_BASE, { method: "POST", body: formData });
      const data = await res.json();
      if (data.ok || data.success) {
        setSubmitted(true);
        // Redirect to user panel after 3 seconds
        setTimeout(() => { window.location.href = USER_PANEL_URL; }, 3000);
      } else { alert(data.error || data.message || "Registration failed"); }
    } catch { alert("Network error. Please try again."); }
    finally { setSubmitting(false); }
  };

  const handleReset = () => {
    setForm(INITIAL_FORM);
    setErrors({}); setSubmitted(false);
    setPhotos([null, null, null]); setPhotoPreviews([null, null, null]);
    setHoroscopePhotos({ rasi: null, amsam: null });
    setHoroscopePreview({ rasi: null, amsam: null });
    setPhotoProgress(0);
    setOpenDropdownId(null);
    setPdfLanguage("en");
  };

  // ─── Helpers ─────────────────────────────────────────────────────────────

  const inputStyle = (hasErr) => ({
    padding: "10px 14px",
    border: `1.5px solid ${hasErr ? "#C41E3A" : "#D4A0A8"}`,
    borderRadius: 8,
    fontSize: "0.88rem",
    fontFamily: "'Lato',sans-serif",
    background: "#FFFAF9",
    color: "#2A0A0E",
    outline: "none",
    width: "100%",
    boxSizing: "border-box",
    transition: "all 0.2s ease",
    boxShadow: hasErr ? "0 0 0 3px rgba(196,30,58,0.1)" : "none",
  });

  const radioStyle = { display: "flex", flexWrap: "wrap", gap: 12 };

  const sectionBox = {
    background: "white", borderRadius: 16, padding: "28px 30px", marginBottom: 20,
    boxShadow: "0 2px 20px rgba(139,0,0,0.06)", border: "1px solid rgba(196,30,58,0.1)",
  };

  const uploadedCount = photos.filter(Boolean).length;

  // ─── Submitted screen ────────────────────────────────────────────────────
  if (submitted) {
    return (
      <>
        <style>{`@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Lato:wght@300;400;700&display=swap');`}</style>
        <div style={{ minHeight: "100vh", background: "linear-gradient(135deg,#FFF5F5 0%,#FFF9F0 50%,#FFF5F5 100%)", display: "flex", alignItems: "center", justifyContent: "center", fontFamily: "'Lato',sans-serif" }}>
          <div style={{ textAlign: "center", padding: 48, background: "white", borderRadius: 24, boxShadow: "0 20px 60px rgba(139,0,0,0.15)", maxWidth: 500, border: "1px solid rgba(196,30,58,0.1)" }}>
            <div style={{ fontSize: 56, marginBottom: 16 }}>🎊</div>
            <h2 style={{ fontFamily: "'Playfair Display',serif", color: "#8B0000", fontSize: "1.8rem", marginBottom: 8 }}>Profile Created!</h2>
            <p style={{ color: "#7A4050", marginBottom: 28, lineHeight: 1.6 }}>Your matrimony profile has been submitted successfully. We will review and activate it shortly.</p>
            <button onClick={handleReset} style={{ background: "linear-gradient(135deg,#8B0000,#C41E3A)", color: "white", border: "none", padding: "12px 32px", borderRadius: 10, fontWeight: 700, cursor: "pointer", fontSize: "0.95rem", letterSpacing: "0.05em" }}>
              Create Another Profile
            </button>
          </div>
        </div>
      </>
    );
  }

  // ─── Main form ───────────────────────────────────────────────────────────
  return (
    <>
      <style>{`
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Lato:wght@300;400;700&display=swap');
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: #F9F0F2; font-family: 'Lato', sans-serif; }
        input:focus, textarea:focus { border-color: #8B0000 !important; box-shadow: 0 0 0 3px rgba(139,0,0,0.12) !important; outline: none; }
        input::placeholder, textarea::placeholder { color: #C0A0A8; font-style: italic; }
        .row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .row-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; }
        .row-4 { display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 16px; }
        .photo-upload-zone { transition: all 0.2s ease; }
        .photo-upload-zone:hover { border-color: #8B0000 !important; background: linear-gradient(135deg,#FFF0F2,#FFF8F0) !important; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .spinner { width: 24px; height: 24px; border-radius: 50%; border: 2.5px solid #e0e0e0; border-top-color: #C41E3A; animation: spin 0.8s linear infinite; }
        @media (max-width: 768px) { .row-2, .row-3, .row-4 { grid-template-columns: 1fr; gap: 16px; } .form-outer { padding: 0 12px 40px; } }
        @media (max-width: 600px) { .form-outer { padding: 0 10px 32px; } input, textarea { font-size: 16px !important; } table th, table td { padding: 8px 6px !important; font-size: 12px !important; } }
        @media (max-width: 480px) { .form-outer { padding: 0 8px 24px; } h1 { font-size: clamp(1.4rem,4vw,2rem) !important; } h2 { font-size: clamp(1rem,3vw,1.3rem) !important; } label { font-size: 0.7rem !important; } table th, table td { padding: 6px 4px !important; font-size: 11px !important; } button { padding: 8px 16px !important; font-size: 0.8rem !important; } }
      `}</style>

      <div className="form-outer" style={{ minHeight: "100vh", background: "linear-gradient(160deg,#F9EEF0 0%,#FFF8F0 50%,#F9EEF0 100%)", padding: "0 16px 48px" }}>
        <div style={{ maxWidth: 860, margin: "0 auto" }}>

          {/* ── Download Form PDF ──────────────────────────────────────── */}
          <div style={{ ...sectionBox, marginBottom: 20, background: "linear-gradient(135deg,#FFF5F5 0%,#FFF9F0 100%)", borderLeft: "4px solid #8B0000" }}>
            <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", gap: 12, flexWrap: "wrap" }}>
              <div style={{ display: "flex", alignItems: "center", gap: 12 }}>
                <div style={{ fontSize: 20 }}>📄</div>
                <div>
                  <h3 style={{ margin: 0, fontSize: "0.95rem", fontFamily: "'Playfair Display',serif", fontWeight: 700, color: "#5A0010", marginBottom: 4 }}>Print or Fill Form Manually</h3>
                  <p style={{ margin: 0, fontSize: "0.8rem", color: "#8A5060", fontStyle: "italic" }}>Download the form as PDF for manual filling and printing</p>
                </div>
              </div>
              <div style={{ display: "flex", gap: 10, alignItems: "center", flexWrap: "wrap" }}>
                <select 
                  value={pdfLanguage} 
                  onChange={e => setPdfLanguage(e.target.value)}
                  disabled={downloadingPDF}
                  style={{
                    padding: "8px 12px",
                    border: "1.5px solid #D4A0A8",
                    borderRadius: 8,
                    fontSize: "0.85rem",
                    fontFamily: "'Lato',sans-serif",
                    background: "#FFFAF9",
                    color: "#2A0A0E",
                    cursor: "pointer",
                    outline: "none",
                  }}
                >
                  <option value="en">English</option>
                  <option value="ta">தமிழ் (Tamil)</option>
                </select>
                <button
                  type="button"
                  onClick={handleDownloadPDF}
                  disabled={downloadingPDF}
                  style={{
                    background: downloadingPDF ? "#aaa" : "linear-gradient(135deg,#8B0000,#C41E3A)",
                    color: "white",
                    border: "none",
                    padding: "8px 20px",
                    borderRadius: 8,
                    fontWeight: 700,
                    cursor: downloadingPDF ? "not-allowed" : "pointer",
                    fontSize: "0.85rem",
                    letterSpacing: "0.05em",
                    boxShadow: "0 4px 12px rgba(139,0,0,0.2)",
                    transition: "all 0.2s",
                    minWidth: 120,
                    whiteSpace: "nowrap"
                  }}
                >
                  {downloadingPDF ? "⏳ Generating..." : "📥 Download PDF"}
                </button>
              </div>
            </div>
          </div>

          {/* ── Photo Upload ───────────────────────────────────────────── */}
          <div style={{ ...sectionBox, marginBottom: 20 }}>
            <SectionHeader icon="📸" title={t("registration.profilePhotographs")} />
            <p style={{ fontSize: "0.82rem", color: "#8A5060", marginBottom: 20, fontStyle: "italic" }}>{t("registration.photoDesc")}</p>

            {uploadedCount < 3 && (
              <label className="photo-upload-zone" style={{ display: "flex", flexDirection: "row", alignItems: "center", justifyContent: "flex-start", gap: 16, cursor: photoValidating ? "not-allowed" : "pointer", borderRadius: 14, background: "linear-gradient(135deg,#fff 0%,#f8fbff 100%)", padding: "clamp(20px,4vw,28px)", marginBottom: uploadedCount > 0 ? 20 : 0, opacity: photoValidating ? 0.75 : 1, pointerEvents: photoValidating ? "none" : "auto", boxShadow: "0 4px 16px rgba(139,0,0,0.1)", border: "2px solid transparent", transition: "all 0.3s ease-in-out" }}>
                <div style={{ width: 60, height: 60, borderRadius: 12, display: "flex", alignItems: "center", justifyContent: "center", background: "linear-gradient(135deg,#fde8ec 0%,#fdeef4 100%)", flexShrink: 0, boxShadow: "0 2px 8px rgba(139,0,0,0.12)" }}>
                  {photoValidating ? <div className="spinner" /> : <span style={{ fontSize: 28 }}>📷</span>}
                </div>
                <div style={{ flex: 1, minWidth: 0 }}>
                  {photoValidating ? (
                    <div style={{ display: "flex", flexDirection: "column", gap: 8 }}>
                      <span style={{ color: "#C41E3A", fontSize: 13, fontWeight: 600 }}>Validating & uploading...</span>
                      <div style={{ width: "100%", height: 6, background: "#e8eef7", borderRadius: 3, overflow: "hidden" }}>
                        <div style={{ width: `${Math.min(photoProgress, 100)}%`, height: "100%", background: "linear-gradient(90deg,#C41E3A,#8B0000)", borderRadius: 3, transition: "width 0.3s ease" }} />
                      </div>
                      <span style={{ fontSize: 11, color: "#666", fontWeight: 500 }}>{photoProgress}% complete</span>
                    </div>
                  ) : (
                    <div>
                      <div style={{ color: "#5A0010", fontSize: 14, fontWeight: 700, marginBottom: 3 }}>Upload Profile Photos</div>
                      <div style={{ color: "#8A5060", fontSize: 12, lineHeight: 1.4 }}>Select up to {3 - uploadedCount} photo{3 - uploadedCount > 1 ? "s" : ""} • Max 5MB each</div>
                    </div>
                  )}
                </div>
                <input type="file" accept="image/*" multiple disabled={photoValidating} onChange={e => handlePhotoUpload(e.target.files)} style={{ display: "none" }} />
              </label>
            )}

            {uploadedCount > 0 && (
              <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fill, minmax(140px, 1fr))", gap: 14 }}>
                {photoPreviews.map((preview, index) =>
                  preview ? (
                    <div key={index} style={{ borderRadius: 12, overflow: "hidden", border: "2px solid #C41E3A", boxShadow: "0 6px 20px rgba(139,0,0,0.14)", position: "relative" }}>
                      <img src={preview} alt={`Photo ${index + 1}`} style={{ width: "100%", height: "clamp(130px,25vw,170px)", objectFit: "cover", display: "block" }} />
                      <div style={{ padding: "8px 10px", background: "linear-gradient(135deg,#8B0000,#C41E3A)", display: "flex", justifyContent: "space-between", alignItems: "center" }}>
                        <span style={{ color: "rgba(255,255,255,0.9)", fontSize: "0.75rem", fontWeight: 600 }}>Photo {index + 1}</span>
                        <button type="button" onClick={() => removePhoto(index)} style={{ background: "rgba(255,255,255,0.2)", color: "white", border: "none", padding: "3px 10px", borderRadius: 20, cursor: "pointer", fontSize: "0.72rem", fontWeight: 700 }}>{t("registration.removePhoto")}</button>
                      </div>
                    </div>
                  ) : null
                )}
                {uploadedCount < 3 && (
                  <label className="photo-upload-zone" style={{ display: "flex", flexDirection: "column", alignItems: "center", justifyContent: "center", gap: 10, cursor: photoValidating ? "not-allowed" : "pointer", border: "2px dashed #D4A0A8", borderRadius: 12, background: "linear-gradient(135deg,#FFF8F9,#FFF5F0)", height: "clamp(130px,25vw,170px)", opacity: photoValidating ? 0.6 : 1, pointerEvents: photoValidating ? "none" : "auto" }}>
                    <div style={{ fontSize: 28 }}>{photoValidating ? "⏳" : "➕"}</div>
                    <div style={{ color: "#8B0000", fontSize: "0.78rem", fontWeight: 700, textAlign: "center" }}>{photoValidating ? "Validating…" : `Add Photo (${3 - uploadedCount} left)`}</div>
                    <input type="file" accept="image/*" multiple disabled={photoValidating} onChange={e => handlePhotoUpload(e.target.files)} style={{ display: "none" }} />
                  </label>
                )}
              </div>
            )}

            <div style={{ marginTop: 14, display: "flex", alignItems: "center", gap: 8 }}>
              {[0, 1, 2].map(i => (
                <div key={i} style={{ width: 10, height: 10, borderRadius: "50%", background: photoPreviews[i] ? "#8B0000" : "#E0C0C8", transition: "background 0.3s" }} />
              ))}
              <span style={{ fontSize: "0.75rem", color: "#9A6070", marginLeft: 6 }}>{uploadedCount} / 3 photo{uploadedCount !== 1 ? "s" : ""} uploaded</span>
            </div>
          </div>

          {/* ── Personal Details ────────────────────────────────────────── */}
          <div style={sectionBox}>
            <SectionHeader icon="👤" title={t("registration.personalInfo")} />
            <div style={{ display: "flex", flexDirection: "column", gap: 20 }}>
              <div className="row-3">
                <FormField label={t("registration.name")} required error={errors.name}>
                  <input value={form.name} onChange={e => set("name", e.target.value)} placeholder={t("registration.namePlaceholder")} style={inputStyle(errors.name)} />
                </FormField>
                <FormField label={t("registration.gender")} required error={errors.gender}>
                  <CustomSelect
                    id="gender"
                    value={form.gender}
                    onChange={v => set("gender", v)}
                    options={GENDERS}
                    hasErr={!!errors.gender}
                    openDropdownId={openDropdownId}
                    setOpenDropdownId={setOpenDropdownId}
                  />
                </FormField>
                <FormField label={t("registration.dob")} required error={errors.dob}>
                  <input type="date" value={form.dob} onChange={e => set("dob", e.target.value)} style={inputStyle(errors.dob)} />
                </FormField>
              </div>

              <div className="row-3">
                <FormField label={t("registration.birthTime")}>
                  <div style={{ display: "flex", gap: 8 }}>
                    <div style={{ flex: 1 }}>
                      <CustomSelect id="birthHour" value={form.birthHour || t("registration.hour")} onChange={v => set("birthHour", v)} options={[t("registration.hour"), ...HOURS]} hasErr={false} openDropdownId={openDropdownId} setOpenDropdownId={setOpenDropdownId} />
                    </div>
                    <div style={{ flex: 1 }}>
                      <CustomSelect id="birthMin" value={form.birthMin || t("registration.minute")} onChange={v => set("birthMin", v)} options={[t("registration.minute"), ...MINUTES]} hasErr={false} openDropdownId={openDropdownId} setOpenDropdownId={setOpenDropdownId} />
                    </div>
                    <div style={{ flex: 1 }}>
                      <CustomSelect id="birthAmPm" value={form.birthAmPm} onChange={v => set("birthAmPm", v)} options={AMPM} hasErr={false} openDropdownId={openDropdownId} setOpenDropdownId={setOpenDropdownId} />
                    </div>
                  </div>
                </FormField>
                <FormField label={t("registration.placeBirth")} required error={errors.placeBirth}>
                  <input value={form.placeBirth} onChange={e => set("placeBirth", e.target.value)} placeholder={t("registration.pleaseSpecify")} style={inputStyle(errors.placeBirth)} />
                </FormField>
                <FormField label={t("registration.nativity")} required error={errors.nativity}>
                  <input value={form.nativity} onChange={e => set("nativity", e.target.value)} list="reg_nativity_list" placeholder="Type or select" style={inputStyle(errors.nativity)} />
                  <datalist id="reg_nativity_list">
                    <option value="India"/><option value="Pondicherry"/><option value="Chennai"/>
                    <option value="Tamil Nadu"/><option value="France"/><option value="Singapore"/>
                    <option value="Malaysia"/><option value="UAE"/><option value="Kuwait"/>
                    <option value="Saudi Arabia"/><option value="Qatar"/><option value="USA"/>
                    <option value="UK"/><option value="Canada"/><option value="Australia"/>
                    <option value="Germany"/><option value="Sri Lanka"/><option value="Other"/>
                  </datalist>
                </FormField>
              </div>

              <div className="row-2">
                <FormField label={t("registration.motherTongue")} required error={errors.motherTongue}>
                  <CustomSelect
                    id="motherTongue"
                    value={form.motherTongue}
                    onChange={v => set("motherTongue", v)}
                    options={MOTHER_TONGUES}
                    hasErr={!!errors.motherTongue}
                    openDropdownId={openDropdownId}
                    setOpenDropdownId={setOpenDropdownId}
                  />
                </FormField>
                <FormField label={t("registration.maritalStatus")} required error={errors.maritalStatus}>
                  <CustomSelect
                    id="maritalStatus"
                    value={form.maritalStatus}
                    onChange={v => set("maritalStatus", v)}
                    options={MARITAL_STATUSES}
                    hasErr={!!errors.maritalStatus}
                    openDropdownId={openDropdownId}
                    setOpenDropdownId={setOpenDropdownId}
                  />
                </FormField>
              </div>

              <FormField label="Additional Details">
                <textarea value={form.others} onChange={e => set("others", e.target.value)} rows={4} placeholder={t("registration.additionalDetailsPlaceholder")} style={{ ...inputStyle(false), resize: "vertical", minHeight: 100, lineHeight: 1.7 }} />
              </FormField>
            </div>
          </div>

          {/* ── Family Details ──────────────────────────────────────────── */}
          <div style={sectionBox}>
            <SectionHeader icon="👨‍👩‍👧‍👦" title={t("registration.familyInfo")} />
            <div style={{ display: "flex", flexDirection: "column", gap: 20 }}>
              <div className="row-3">
                <FormField label={t("registration.fatherName")}>
                  <input value={form.fatherName} onChange={e => set("fatherName", e.target.value)} placeholder={t("registration.fatherNamePlaceholder")} style={inputStyle(false)} />
                </FormField>
                <FormField label={t("registration.fatherOccupation")}>
                  <input value={form.fatherJob} onChange={e => set("fatherJob", e.target.value)} placeholder={t("registration.fatherOccupationPlaceholder")} style={inputStyle(false)} />
                </FormField>
                <FormField label={t("registration.fatherAlive")}>
                  <div style={radioStyle}>
                    <RadioOpt name="fatherAlive" value="yes" checked={form.fatherAlive === "yes"} onChange={() => set("fatherAlive", "yes")} label={t("registration.fatherAliveYes")} />
                    <RadioOpt name="fatherAlive" value="no" checked={form.fatherAlive === "no"} onChange={() => set("fatherAlive", "no")} label={t("registration.fatherAliveNo")} />
                  </div>
                </FormField>
              </div>
              <div className="row-3">
                <FormField label={t("registration.motherName")}>
                  <input value={form.motherName} onChange={e => set("motherName", e.target.value)} placeholder={t("registration.motherNamePlaceholder")} style={inputStyle(false)} />
                </FormField>
                <FormField label={t("registration.motherOccupation")}>
                  <input value={form.motherJob} onChange={e => set("motherJob", e.target.value)} placeholder={t("registration.motherOccupationPlaceholder")} style={inputStyle(false)} />
                </FormField>
                <FormField label={t("registration.motherAlive")}>
                  <div style={radioStyle}>
                    <RadioOpt name="motherAlive" value="yes" checked={form.motherAlive === "yes"} onChange={() => set("motherAlive", "yes")} label={t("registration.motherAliveYes")} />
                    <RadioOpt name="motherAlive" value="no" checked={form.motherAlive === "no"} onChange={() => set("motherAlive", "no")} label={t("registration.motherAliveNo")} />
                  </div>
                </FormField>
              </div>

              {/* Siblings table — uses compact CustomSelect */}
              <div>
                <label style={{ fontSize: "0.72rem", fontWeight: 700, color: "#7A1020", textTransform: "uppercase", letterSpacing: "0.08em", display: "block", marginBottom: 10 }}>{t("registration.siblings")}</label>
                <div style={{ overflowX: "auto", borderRadius: 10, border: "1px solid rgba(196,30,58,0.15)" }}>
                  <table style={{ width: "100%", borderCollapse: "collapse", fontSize: "0.84rem" }}>
                    <thead>
                      <tr style={{ background: "linear-gradient(135deg,#8B0000,#C41E3A)" }}>
                        <th style={{ padding: "12px 16px", color: "white", fontWeight: 700, textAlign: "left", fontSize: "0.8rem", letterSpacing: "0.05em" }}>Status</th>
                        {[t("registration.elderBrother"), t("registration.youngerBrother"), t("registration.elderSister"), t("registration.youngerSister")].map(h => (
                          <th key={h} style={{ padding: "12px 16px", color: "white", fontWeight: 600, textAlign: "center", fontSize: "0.78rem" }}>{h}</th>
                        ))}
                      </tr>
                    </thead>
                    <tbody>
                      {[
                        { label: t("registration.sibMarried"), keys: ["sibMarriedEB", "sibMarriedYB", "sibMarriedES", "sibMarriedYS"] },
                        { label: t("registration.sibUnmarried"), keys: ["sibUnmarriedEB", "sibUnmarriedYB", "sibUnmarriedES", "sibUnmarriedYS"] },
                      ].map((row, ri) => (
                        <tr key={ri} style={{ background: ri % 2 === 0 ? "#FFF8F9" : "white" }}>
                          <td style={{ padding: "10px 16px", fontWeight: 700, color: "#5A0010", fontSize: "0.82rem" }}>{row.label}</td>
                          {row.keys.map(k => (
                            <td key={k} style={{ padding: "8px 12px", textAlign: "center" }}>
                              <input
                                type="text"
                                value={form[k]}
                                onChange={e => set(k, e.target.value)}
                                placeholder=""
                                style={{
                                  padding: "8px 10px",
                                  border: "1.5px solid #D4A0A8",
                                  borderRadius: 6,
                                  fontSize: "0.82rem",
                                  fontFamily: "'Lato',sans-serif",
                                  background: "#FFFAF9",
                                  color: "#2A0A0E",
                                  outline: "none",
                                  width: "100%",
                                  boxSizing: "border-box",
                                  textAlign: "center",
                                  transition: "all 0.2s ease"
                                }}
                              />
                            </td>
                          ))}
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

          {/* ── Physical Attributes ─────────────────────────────────────── */}
          <div style={sectionBox}>
            <SectionHeader icon="⚖️" title={t("registration.physicalAttributes")} />
            <div style={{ display: "flex", flexDirection: "column", gap: 20 }}>
              <div className="row-3">
                <FormField label={t("registration.height")}>
                  <CustomSelect id="height" value={form.height} onChange={v => set("height", v)} options={HEIGHTS} hasErr={false} openDropdownId={openDropdownId} setOpenDropdownId={setOpenDropdownId} />
                </FormField>
                <FormField label={t("registration.weight")}>
                  <CustomSelect id="weight" value={form.weight} onChange={v => set("weight", v)} options={WEIGHTS} hasErr={false} openDropdownId={openDropdownId} setOpenDropdownId={setOpenDropdownId} />
                </FormField>
                <FormField label={t("registration.bloodGroup")}>
                  <CustomSelect id="bloodGroup" value={form.bloodGroup} onChange={v => set("bloodGroup", v)} options={BLOOD_GROUPS} hasErr={false} openDropdownId={openDropdownId} setOpenDropdownId={setOpenDropdownId} />
                </FormField>
              </div>
              <div className="row-2">
                <FormField label={t("registration.diet")}>
                  <div style={radioStyle}>
                    {[t("registration.dietVegetarian"), t("registration.dietNonVegetarian"), t("registration.dietEggetarian")].map(d => (
                      <RadioOpt key={d} name="diet" value={d} checked={form.diet === d} onChange={() => set("diet", d)} label={d} />
                    ))}
                  </div>
                </FormField>
                <FormField label={t("registration.disability")}>
                  <div style={radioStyle}>
                    {[t("registration.disabilityNo"), t("registration.disabilityYes")].map(d => (
                      <RadioOpt key={d} name="disability" value={d} checked={form.disability === d} onChange={() => set("disability", d)} label={d} />
                    ))}
                  </div>
                </FormField>
              </div>
              <FormField label={t("registration.complexion")}>
                <div style={{ ...radioStyle, flexWrap: "wrap" }}>
                  {[t("registration.complexionVeryFair"), t("registration.complexionFair"), t("registration.complexionWheatish"), t("registration.complexionWheatishBrown"), t("registration.complexionDark")].map(c => (
                    <RadioOpt key={c} name="complexion" value={c} checked={form.complexion === c} onChange={() => set("complexion", c)} label={c} />
                  ))}
                </div>
              </FormField>
            </div>
          </div>

          {/* ── Education & Occupation ──────────────────────────────────── */}
          <div style={sectionBox}>
            <SectionHeader icon="🎓" title={t("registration.educationOccupation")} />
            <div style={{ display: "flex", flexDirection: "column", gap: 20 }}>
              <div className="row-3">
                <FormField label={t("registration.qualification")}>
                  <input value={form.qualification} onChange={e => set("qualification", e.target.value)} placeholder={t("registration.qualificationPlaceholder")} style={inputStyle(false)} />
                </FormField>
                <FormField label={t("registration.occupation")}>
                  <input value={form.job} onChange={e => set("job", e.target.value)} placeholder={t("registration.occupationPlaceholder")} style={inputStyle(false)} />
                </FormField>
                <FormField label={t("registration.placeOfWork")}>
                  <input value={form.placeJob} onChange={e => set("placeJob", e.target.value)} placeholder={t("registration.placeOfWorkPlaceholder")} style={inputStyle(false)} />
                </FormField>
              </div>
              <div style={{ maxWidth: 300 }}>
                <FormField label={t("registration.monthlyIncome")}>
                  <input value={form.incomeMonth} onChange={e => set("incomeMonth", e.target.value)} placeholder={t("registration.monthlyIncomePlaceholder")} style={inputStyle(false)} />
                </FormField>
              </div>
            </div>
          </div>

          {/* ── Astrology ───────────────────────────────────────────────── */}
          <div style={sectionBox}>
            <SectionHeader icon="🪐" title={t("registration.astrology")} />
            <div style={{ display: "flex", flexDirection: "column", gap: 20 }}>
              <div className="row-4">
                <FormField label={t("registration.caste")} required>
                  <CustomSelect id="caste" value={form.caste} onChange={v => set("caste", v)} options={CASTES} hasErr={false} openDropdownId={openDropdownId} setOpenDropdownId={setOpenDropdownId} />
                </FormField>
                <FormField label={t("registration.subcaste")} required>
                  <CustomSelect id="subCaste" value={form.subCaste} onChange={v => set("subCaste", v)} options={SUB_CASTES} hasErr={false} openDropdownId={openDropdownId} setOpenDropdownId={setOpenDropdownId} />
                </FormField>
                <FormField label={t("registration.gothram")}>
                  <input value={form.gothram} onChange={e => set("gothram", e.target.value)} placeholder={t("registration.gothramPlaceholder")} style={inputStyle(false)} />
                </FormField>
                <FormField label={t("registration.star")}>
                  <CustomSelect id="star" value={form.star} onChange={v => set("star", v)} options={STARS} hasErr={false} openDropdownId={openDropdownId} setOpenDropdownId={setOpenDropdownId} />
                </FormField>
              </div>
              <div className="row-4">
                <FormField label={t("registration.raasi")}>
                  <CustomSelect id="raasi" value={form.raasi} onChange={v => set("raasi", v)} options={RASI_LIST} hasErr={false} openDropdownId={openDropdownId} setOpenDropdownId={setOpenDropdownId} />
                </FormField>
                <FormField label={t("registration.padam")}>
                  <CustomSelect id="padam" value={form.padam} onChange={v => set("padam", v)} options={PADAM_LIST} hasErr={false} openDropdownId={openDropdownId} setOpenDropdownId={setOpenDropdownId} />
                </FormField>
                <FormField label={t("registration.laknam")}>
                  <CustomSelect id="laknam" value={form.laknam} onChange={v => set("laknam", v)} options={LAKNAM} hasErr={false} openDropdownId={openDropdownId} setOpenDropdownId={setOpenDropdownId} />
                </FormField>
                <FormField label={t("registration.dosham")}>
                  <CustomSelect id="dosham" value={form.dosham} onChange={v => set("dosham", v)} options={DOSHAM_LIST} hasErr={false} openDropdownId={openDropdownId} setOpenDropdownId={setOpenDropdownId} />
                </FormField>
              </div>
            </div>
          </div>

          {/* ── Horoscope Photos ────────────────────────────────────────── */}
          <div style={sectionBox}>
            <SectionHeader icon="🔮" title={t("registration.horoscopeDetails")} />
            <p style={{ fontSize: "0.82rem", color: "#8A5060", marginBottom: 20, fontStyle: "italic" }}>{t("registration.horoscopeDesc")}</p>
            <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fit, minmax(200px, 1fr))", gap: "clamp(12px,3vw,20px)" }}>
              {[
                { key: "rasi", label: t("registration.rasiChart"), icon: "♈", desc: t("registration.janmaKundali") },
                { key: "amsam", label: t("registration.amsamChart"), icon: "⭐", desc: t("registration.navamsaChart") },
              ].map(({ key, label, icon, desc }) => (
                <div key={key}>
                  {horoscopePreview[key] ? (
                    <div style={{ borderRadius: 12, overflow: "hidden", border: "2px solid #C41E3A", boxShadow: "0 8px 24px rgba(139,0,0,0.12)" }}>
                      <img src={horoscopePreview[key]} alt={label} style={{ width: "100%", height: "clamp(150px,25vw,180px)", objectFit: "cover", display: "block" }} />
                      <div style={{ padding: "clamp(8px,2vw,10px) clamp(10px,2vw,12px)", background: "linear-gradient(135deg,#8B0000,#C41E3A)", display: "flex", justifyContent: "space-between", alignItems: "center" }}>
                        <span style={{ color: "white", fontSize: "clamp(0.75rem,2vw,0.82rem)", fontWeight: 600 }}>{label}</span>
                        <button type="button" onClick={() => removeHoroscopePhoto(key)} style={{ background: "rgba(255,255,255,0.2)", color: "white", border: "none", padding: "3px 12px", borderRadius: 20, cursor: "pointer", fontSize: "0.75rem", fontWeight: 600 }}>{t("registration.removePhoto")}</button>
                      </div>
                    </div>
                  ) : (
                    <label style={{ display: "flex", flexDirection: "column", alignItems: "center", justifyContent: "center", gap: "clamp(10px,2vw,12px)", cursor: "pointer", height: "clamp(150px,25vw,180px)", border: "2px dashed #D4A0A8", borderRadius: 12, background: "linear-gradient(135deg,#FFF8F9,#FFF5F0)", padding: "clamp(12px,2vw,16px)" }}>
                      <div style={{ width: 52, height: 52, borderRadius: "50%", background: "rgba(139,0,0,0.08)", display: "flex", alignItems: "center", justifyContent: "center", fontSize: 24 }}>{icon}</div>
                      <div style={{ textAlign: "center" }}>
                        <div style={{ color: "#8B0000", fontWeight: 700, fontSize: "clamp(0.8rem,2vw,0.9rem)" }}>{label}</div>
                        <div style={{ color: "#B08090", fontSize: "clamp(0.65rem,1.5vw,0.72rem)", marginTop: 2 }}>{desc} · Click to upload</div>
                      </div>
                      <div style={{ background: "linear-gradient(135deg,#8B0000,#C41E3A)", color: "white", padding: "6px 16px", borderRadius: 20, fontSize: "clamp(0.7rem,1.5vw,0.78rem)", fontWeight: 700 }}>{t("registration.uploadChart")}</div>
                      <input type="file" accept="image/*" onChange={e => handleHoroscopePhotoUpload(key, e.target.files[0])} style={{ display: "none" }} />
                    </label>
                  )}
                </div>
              ))}
            </div>
          </div>

          {/* ── Partner Expectations ────────────────────────────────────── */}
          <div style={sectionBox}>
            <SectionHeader icon="💑" title={t("registration.partnerExpectations")} />
            <div style={{ display: "flex", flexDirection: "column", gap: 20 }}>
              <div className="row-2">
                <FormField label={t("registration.partnerQualification")}>
                  <input value={form.partnerQualification} onChange={e => set("partnerQualification", e.target.value)} placeholder={t("registration.partnerQualificationPlaceholder")} style={inputStyle(false)} />
                </FormField>
                <FormField label={t("registration.partnerJobPreference")}>
                  <div style={{ display: "flex", gap: 8 }}>
                    <input value={form.partnerJob} onChange={e => set("partnerJob", e.target.value)} placeholder={t("registration.partnerJobPlaceholder")} style={{ ...inputStyle(false), flex: 1 }} />
                    <div style={{ width: 130, flexShrink: 0 }}>
                      <CustomSelect
                        id="partnerJobRequirement"
                        value={form.partnerJobRequirement}
                        onChange={v => set("partnerJobRequirement", v)}
                        options={JOB_REQUIREMENT_OPTIONS}
                        hasErr={false}
                        openDropdownId={openDropdownId}
                        setOpenDropdownId={setOpenDropdownId}
                      />
                    </div>
                  </div>
                </FormField>
              </div>

              <div className="row-3">
                <FormField label={t("registration.partnerIncomeExpectation")}>
                  <input value={form.partnerIncomeMonth} onChange={e => set("partnerIncomeMonth", e.target.value)} placeholder={t("registration.partnerIncomeMonthPlaceholder")} style={inputStyle(false)} />
                </FormField>
                <FormField label={t("registration.preferredAgeFrom")}>
                  <input value={form.partnerAgeFrom} onChange={e => set("partnerAgeFrom", e.target.value)} placeholder={t("registration.partnerAgeFromPlaceholder")} style={inputStyle(false)} />
                </FormField>
                <FormField label={t("registration.preferredAgeTo")}>
                  <input value={form.partnerAgeTo} onChange={e => set("partnerAgeTo", e.target.value)} placeholder={t("registration.partnerAgePlaceholder")} style={inputStyle(false)} />
                </FormField>
              </div>

              <div className="row-3">
                <FormField label={t("registration.preferredDiet")}>
                  <CustomSelect id="partnerDiet" value={form.partnerDiet} onChange={v => set("partnerDiet", v)} options={DIET_OPTIONS} hasErr={false} openDropdownId={openDropdownId} setOpenDropdownId={setOpenDropdownId} />
                </FormField>
                <FormField label={t("registration.preferredCaste")}>
                  <CustomSelect id="partnerCaste" value={form.partnerCaste} onChange={v => set("partnerCaste", v)} options={PARTNER_CASTE_OPTIONS} hasErr={false} openDropdownId={openDropdownId} setOpenDropdownId={setOpenDropdownId} />
                </FormField>
                <FormField label={t("registration.partnerMaritalStatus")}>
                  <CustomSelect id="partnerMaritalStatus" value={form.partnerMaritalStatus} onChange={v => set("partnerMaritalStatus", v)} options={PARTNER_MARITAL_OPTIONS} hasErr={false} openDropdownId={openDropdownId} setOpenDropdownId={setOpenDropdownId} />
                </FormField>
              </div>

              <div className="row-2">
                <FormField label={t("registration.horoscopeRequired")}>
                  <div style={radioStyle}>
                    {["Yes", "No"].map(v => (
                      <RadioOpt key={v} name="partnerHoroscope" value={v} checked={form.partnerHoroscopeRequired === v} onChange={() => set("partnerHoroscopeRequired", v)} label={v} />
                    ))}
                  </div>
                </FormField>
                <FormField label={t("registration.subCastePreference")}>
                  <CustomSelect id="partnerSubCaste" value={form.partnerSubCaste} onChange={v => set("partnerSubCaste", v)} options={PARTNER_SUB_CASTE_OPTIONS} hasErr={false} openDropdownId={openDropdownId} setOpenDropdownId={setOpenDropdownId} />
                </FormField>
              </div>

              <FormField label="Any Other Requirements">
                <textarea value={form.partnerOtherRequirement} onChange={e => set("partnerOtherRequirement", e.target.value)} rows={3} placeholder={t("registration.partnerRequirementsPlaceholder")} style={{ ...inputStyle(false), resize: "vertical", minHeight: 80, lineHeight: 1.7 }} />
              </FormField>
            </div>
          </div>

          {/* ── Communication Details ───────────────────────────────────── */}
          <div style={sectionBox}>
            <SectionHeader icon="📞" title={t("registration.communicationDetails")} />
            <div style={{ display: "flex", flexDirection: "column", gap: 20 }}>
              <div className="row-2">
                <FormField label={t("registration.permanentAddress")}>
                  <textarea value={form.permanentAddress} onChange={e => set("permanentAddress", e.target.value)} placeholder={t("registration.permanentAddressPlaceholder")} rows={3} style={{ ...inputStyle(false), resize: "vertical", minHeight: 88, lineHeight: 1.6 }} />
                </FormField>
                <FormField label={t("registration.presentAddress")}>
                  <textarea value={form.presentAddress} onChange={e => set("presentAddress", e.target.value)} placeholder={t("registration.presentAddressPlaceholder")} rows={3} style={{ ...inputStyle(false), resize: "vertical", minHeight: 88, lineHeight: 1.6 }} />
                </FormField>
              </div>
              <div className="row-2">
                <FormField label={t("registration.contactPerson")}>
                  <input value={form.contactPerson} onChange={e => set("contactPerson", e.target.value)} placeholder={t("registration.contactPersonPlaceholder")} style={inputStyle(false)} />
                </FormField>
                <FormField label={t("registration.contactNumber")} required error={errors.contactNumber}>
                  <input value={form.contactNumber} onChange={e => set("contactNumber", e.target.value)} placeholder={t("registration.contactNumberPlaceholder")} maxLength={10} style={inputStyle(errors.contactNumber)} />
                </FormField>
              </div>
            </div>
          </div>

          {/* ── Footer Buttons ──────────────────────────────────────────── */}
          <div style={{ display: "flex", gap: 14, justifyContent: "center", padding: "10px 0 8px", flexWrap: "wrap" }}>
            <button type="button" onClick={handleReset} style={{ background: "white", color: "#8B0000", border: "2px solid #8B0000", padding: "13px 36px", borderRadius: 10, fontWeight: 700, cursor: "pointer", fontSize: "0.95rem", letterSpacing: "0.06em", transition: "all 0.2s", minWidth: 160 }}>
              ↺ {t("registration.reset")}
            </button>
            <button type="button" onClick={handleSubmit} disabled={submitting} style={{ background: submitting ? "#aaa" : "linear-gradient(135deg,#5A0010,#8B0000 40%,#C41E3A)", color: "white", border: "none", padding: "13px 44px", borderRadius: 10, fontWeight: 700, cursor: submitting ? "not-allowed" : "pointer", fontSize: "0.95rem", letterSpacing: "0.06em", boxShadow: "0 6px 20px rgba(139,0,0,0.35)", transition: "all 0.2s", minWidth: 200 }}>
              {submitting ? "Submitting..." : `✦ ${t("registration.submit")}`}
            </button>
          </div>
          <p style={{ textAlign: "center", color: "#C4A0A8", fontSize: "0.75rem", marginTop: 12 }}>
            <span style={{ color: "#C41E3A" }}>✦</span> Fields marked with ✦ are mandatory
          </p>

        </div>
      </div>
    </>
  );
}