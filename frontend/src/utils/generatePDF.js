// jspdf (~200 KB) and html2canvas (~150 KB) are dynamically imported below so
// they don't bloat the initial JS payload. Only downloaded when the user
// actually clicks the "Download PDF" button.

export const generateFormPDF = async (language = "en") => {
  // Dynamic imports run in parallel; both libs fetched on first call.
  const [{ default: jsPDF }, { default: html2canvas }] = await Promise.all([
    import("jspdf"),
    import("html2canvas"),
  ]);
  const translations = {
    en: {
      title: "MATRIMONY REGISTRATION FORM",
      personalInfo: "PERSONAL INFORMATION",
      name: "Name",
      gender: "Gender",
      dob: "Date of Birth",
      birthPlace: "Birth Place",
      nativity: "Nativity",
      motherTongue: "Mother Tongue",
      maritalStatus: "Marital Status",
      familyInfo: "FAMILY INFORMATION",
      fatherName: "Father's Name",
      fatherOccupation: "Father's Occupation",
      motherName: "Mother's Name",
      motherOccupation: "Mother's Occupation",
      physicalInfo: "PHYSICAL ATTRIBUTES",
      height: "Height",
      weight: "Weight",
      bloodGroup: "Blood Group",
      diet: "Diet",
      disability: "Disability",
      complexion: "Complexion",
      education: "EDUCATION & OCCUPATION",
      qualification: "Qualification",
      occupation: "Occupation",
      placeOfWork: "Place of Work",
      monthlyIncome: "Monthly Income",
      astrology: "ASTROLOGY DETAILS",
      caste: "Caste",
      subCaste: "Sub-Caste",
      gothram: "Gothram",
      star: "Nakshatra/Star",
      rasi: "Rasi",
      padam: "Padam",
      lagnam: "Lagnam",
      dosham: "Dosham",
      expectations: "PARTNER EXPECTATIONS",
      partnerQual: "Partner's Qualification",
      partnerJob: "Partner's Job",
      partnerIncome: "Expected Income",
      ageFrom: "Age From",
      ageTo: "Age To",
      partnerDiet: "Diet Preference",
      partnerCaste: "Caste Preference",
      contact: "CONTACT DETAILS",
      address: "Address",
      contactPerson: "Contact Person",
      contactNumber: "Contact Number",
      remarks: "Additional Remarks",
    },
    ta: {
      title: "மணமக்கள் பதிவு படிவம்",
      personalInfo: "தனிப்பட்ட தகவல்",
      name: "பெயர்",
      gender: "பாலினம்",
      dob: "பிறந்த தேதி",
      birthPlace: "பிறந்த இடம்",
      nativity: "தாயகம்",
      motherTongue: "தாய்மொழி",
      maritalStatus: "வாழ்க்கை நிலை",
      familyInfo: "குடும்ப தகவல்",
      fatherName: "தந்தையின் பெயர்",
      fatherOccupation: "தந்தையின் தொழில்",
      motherName: "தாயின் பெயர்",
      motherOccupation: "தாயின் தொழில்",
      physicalInfo: "உடல் அளவு",
      height: "உயரம்",
      weight: "எடை",
      bloodGroup: "இரத்த குழு",
      diet: "உணவு முறை",
      disability: "இயலாமை",
      complexion: "நிற நிலை",
      education: "கல்வி & தொழில்",
      qualification: "தகுதி",
      occupation: "தொழில்",
      placeOfWork: "பணிபுரியும் இடம்",
      monthlyIncome: "மாத வருமானம்",
      astrology: "ஜோதிட விவரங்கள்",
      caste: "சாதி",
      subCaste: "உபசாதி",
      gothram: "கோத்திரம்",
      star: "நக்ষத்திரம்",
      rasi: "ராசி",
      padam: "பாதம்",
      lagnam: "லக்னம்",
      dosham: "தோஷம்",
      expectations: "பதிவு நிபந்தனை",
      partnerQual: "பெண்ணின் தகுதி",
      partnerJob: "பெண்ணின் தொழில்",
      partnerIncome: "எதிர்பார்க்கும் வருமானம்",
      ageFrom: "வயது (குறைந்தபட்சம்)",
      ageTo: "வயது (அதிகபட்சம்)",
      partnerDiet: "உணவு முறை விருப்பம்",
      partnerCaste: "சாதி விருப்பம்",
      contact: "தொடர்புத் தகவல்",
      address: "முகவரி",
      contactPerson: "தொடர்புப் பெயர்",
      contactNumber: "தொடர்பு எண்",
      remarks: "மற்ற குறிப்புகள்",
    },
  };

  const t = translations[language] || translations.en;

  const htmlContent = document.createElement("div");
  htmlContent.style.cssText = `
    width: 8.5in;
    height: 11in;
    padding: 0.5in;
    font-family: Arial, sans-serif;
    font-size: 10px;
    line-height: 1.4;
    color: #333;
    background: white;
  `;

  const formHTML = `
    <div style="text-align: center; margin-bottom: 0.2in; border-bottom: 2px solid #8B0000;">
      <h1 style="margin: 0; font-size: 14px; color: #8B0000;">${t.title}</h1>
    </div>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 0.15in;">
      <tr>
        <td style="width: 50%; padding: 4px; border: 1px solid #ccc; font-weight: bold; background: #f5f5f5; font-size: 9px;">${t.name}:</td>
        <td style="width: 50%; padding: 4px; border: 1px solid #ccc; height: 20px;"></td>
      </tr>
      <tr>
        <td style="width: 33%; padding: 4px; border: 1px solid #ccc; font-weight: bold; background: #f5f5f5; font-size: 9px;">${t.gender}:</td>
        <td style="width: 33%; padding: 4px; border: 1px solid #ccc; height: 20px;"></td>
        <td style="width: 34%; padding: 4px; border: 1px solid #ccc; font-weight: bold; background: #f5f5f5; font-size: 9px;">${t.dob}:</td>
        <td style="width: 33%; padding: 4px; border: 1px solid #ccc; height: 20px;"></td>
      </tr>
      <tr>
        <td style="padding: 4px; border: 1px solid #ccc; font-weight: bold; background: #f5f5f5; font-size: 9px;">${t.birthPlace}:</td>
        <td style="padding: 4px; border: 1px solid #ccc; height: 20px;"></td>
      </tr>
      <tr>
        <td style="padding: 4px; border: 1px solid #ccc; font-weight: bold; background: #f5f5f5; font-size: 9px;">${t.nativity}:</td>
        <td style="padding: 4px; border: 1px solid #ccc; height: 20px;"></td>
      </tr>
      <tr>
        <td style="padding: 4px; border: 1px solid #ccc; font-weight: bold; background: #f5f5f5; font-size: 9px;">${t.motherTongue}:</td>
        <td style="padding: 4px; border: 1px solid #ccc; height: 20px;"></td>
      </tr>
      <tr>
        <td style="padding: 4px; border: 1px solid #ccc; font-weight: bold; background: #f5f5f5; font-size: 9px;">${t.maritalStatus}:</td>
        <td style="padding: 4px; border: 1px solid #ccc; height: 20px;"></td>
      </tr>
    </table>

    <h3 style="margin: 0.1in 0 0.05in 0; font-size: 11px; color: #8B0000; border-bottom: 1px solid #8B0000;">${t.familyInfo}</h3>
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 0.15in;">
      <tr>
        <td style="width: 50%; padding: 4px; border: 1px solid #ccc; font-weight: bold; background: #f5f5f5; font-size: 9px;">${t.fatherName}:</td>
        <td style="width: 50%; padding: 4px; border: 1px solid #ccc; height: 20px;"></td>
      </tr>
      <tr>
        <td style="padding: 4px; border: 1px solid #ccc; font-weight: bold; background: #f5f5f5; font-size: 9px;">${t.fatherOccupation}:</td>
        <td style="padding: 4px; border: 1px solid #ccc; height: 20px;"></td>
      </tr>
      <tr>
        <td style="padding: 4px; border: 1px solid #ccc; font-weight: bold; background: #f5f5f5; font-size: 9px;">${t.motherName}:</td>
        <td style="padding: 4px; border: 1px solid #ccc; height: 20px;"></td>
      </tr>
      <tr>
        <td style="padding: 4px; border: 1px solid #ccc; font-weight: bold; background: #f5f5f5; font-size: 9px;">${t.motherOccupation}:</td>
        <td style="padding: 4px; border: 1px solid #ccc; height: 20px;"></td>
      </tr>
    </table>

    <h3 style="margin: 0.1in 0 0.05in 0; font-size: 11px; color: #8B0000; border-bottom: 1px solid #8B0000;">${t.physicalInfo}</h3>
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 0.15in;">
      <tr>
        <td style="width: 25%; padding: 4px; border: 1px solid #ccc; font-weight: bold; background: #f5f5f5; font-size: 9px;">${t.height}:</td>
        <td style="width: 25%; padding: 4px; border: 1px solid #ccc; height: 18px;"></td>
        <td style="width: 25%; padding: 4px; border: 1px solid #ccc; font-weight: bold; background: #f5f5f5; font-size: 9px;">${t.weight}:</td>
        <td style="width: 25%; padding: 4px; border: 1px solid #ccc; height: 18px;"></td>
      </tr>
      <tr>
        <td style="padding: 4px; border: 1px solid #ccc; font-weight: bold; background: #f5f5f5; font-size: 9px;">${t.bloodGroup}:</td>
        <td style="padding: 4px; border: 1px solid #ccc; height: 18px;"></td>
        <td style="padding: 4px; border: 1px solid #ccc; font-weight: bold; background: #f5f5f5; font-size: 9px;">${t.diet}:</td>
        <td style="padding: 4px; border: 1px solid #ccc; height: 18px;"></td>
      </tr>
      <tr>
        <td style="padding: 4px; border: 1px solid #ccc; font-weight: bold; background: #f5f5f5; font-size: 9px;">${t.complexion}:</td>
        <td colspan="3" style="padding: 4px; border: 1px solid #ccc; height: 18px;"></td>
      </tr>
    </table>

    <h3 style="margin: 0.1in 0 0.05in 0; font-size: 11px; color: #8B0000; border-bottom: 1px solid #8B0000;">${t.education}</h3>
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 0.15in;">
      <tr>
        <td style="width: 50%; padding: 4px; border: 1px solid #ccc; font-weight: bold; background: #f5f5f5; font-size: 9px;">${t.qualification}:</td>
        <td style="width: 50%; padding: 4px; border: 1px solid #ccc; height: 20px;"></td>
      </tr>
      <tr>
        <td style="padding: 4px; border: 1px solid #ccc; font-weight: bold; background: #f5f5f5; font-size: 9px;">${t.occupation}:</td>
        <td style="padding: 4px; border: 1px solid #ccc; height: 20px;"></td>
      </tr>
      <tr>
        <td style="padding: 4px; border: 1px solid #ccc; font-weight: bold; background: #f5f5f5; font-size: 9px;">${t.placeOfWork}:</td>
        <td style="padding: 4px; border: 1px solid #ccc; height: 20px;"></td>
      </tr>
      <tr>
        <td style="padding: 4px; border: 1px solid #ccc; font-weight: bold; background: #f5f5f5; font-size: 9px;">${t.monthlyIncome}:</td>
        <td style="padding: 4px; border: 1px solid #ccc; height: 20px;"></td>
      </tr>
    </table>

    <h3 style="margin: 0.1in 0 0.05in 0; font-size: 11px; color: #8B0000; border-bottom: 1px solid #8B0000;">${t.astrology}</h3>
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 0.15in;">
      <tr>
        <td style="width: 25%; padding: 4px; border: 1px solid #ccc; font-weight: bold; background: #f5f5f5; font-size: 9px;">${t.caste}:</td>
        <td style="width: 25%; padding: 4px; border: 1px solid #ccc; height: 18px;"></td>
        <td style="width: 25%; padding: 4px; border: 1px solid #ccc; font-weight: bold; background: #f5f5f5; font-size: 9px;">${t.subCaste}:</td>
        <td style="width: 25%; padding: 4px; border: 1px solid #ccc; height: 18px;"></td>
      </tr>
      <tr>
        <td style="padding: 4px; border: 1px solid #ccc; font-weight: bold; background: #f5f5f5; font-size: 9px;">${t.gothram}:</td>
        <td colspan="3" style="padding: 4px; border: 1px solid #ccc; height: 18px;"></td>
      </tr>
      <tr>
        <td style="padding: 4px; border: 1px solid #ccc; font-weight: bold; background: #f5f5f5; font-size: 9px;">${t.star}:</td>
        <td style="padding: 4px; border: 1px solid #ccc; height: 18px;"></td>
        <td style="padding: 4px; border: 1px solid #ccc; font-weight: bold; background: #f5f5f5; font-size: 9px;">${t.rasi}:</td>
        <td style="padding: 4px; border: 1px solid #ccc; height: 18px;"></td>
      </tr>
    </table>

    <h3 style="margin: 0.1in 0 0.05in 0; font-size: 11px; color: #8B0000; border-bottom: 1px solid #8B0000;">${t.expectations}</h3>
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 0.15in;">
      <tr>
        <td style="width: 50%; padding: 4px; border: 1px solid #ccc; font-weight: bold; background: #f5f5f5; font-size: 9px;">${t.partnerQual}:</td>
        <td style="width: 50%; padding: 4px; border: 1px solid #ccc; height: 18px;"></td>
      </tr>
      <tr>
        <td style="padding: 4px; border: 1px solid #ccc; font-weight: bold; background: #f5f5f5; font-size: 9px;">${t.ageFrom}:</td>
        <td style="padding: 4px; border: 1px solid #ccc; height: 18px;"></td>
      </tr>
      <tr>
        <td style="padding: 4px; border: 1px solid #ccc; font-weight: bold; background: #f5f5f5; font-size: 9px;">${t.ageTo}:</td>
        <td style="padding: 4px; border: 1px solid #ccc; height: 18px;"></td>
      </tr>
      <tr>
        <td style="padding: 4px; border: 1px solid #ccc; font-weight: bold; background: #f5f5f5; font-size: 9px;">${t.partnerCaste}:</td>
        <td style="padding: 4px; border: 1px solid #ccc; height: 18px;"></td>
      </tr>
    </table>

    <h3 style="margin: 0.1in 0 0.05in 0; font-size: 11px; color: #8B0000; border-bottom: 1px solid #8B0000;">${t.contact}</h3>
    <table style="width: 100%; border-collapse: collapse;">
      <tr>
        <td style="padding: 4px; border: 1px solid #ccc; font-weight: bold; background: #f5f5f5; font-size: 9px;">${t.contactPerson}:</td>
        <td style="padding: 4px; border: 1px solid #ccc; height: 18px;"></td>
      </tr>
      <tr>
        <td style="padding: 4px; border: 1px solid #ccc; font-weight: bold; background: #f5f5f5; font-size: 9px;">${t.contactNumber}:</td>
        <td style="padding: 4px; border: 1px solid #ccc; height: 18px;"></td>
      </tr>
    </table>
  `;

  htmlContent.innerHTML = formHTML;
  document.body.appendChild(htmlContent);

  try {
    const canvas = await html2canvas(htmlContent, {
      scale: 2,
      backgroundColor: "#ffffff",
      useCORS: true,
    });

    const pdf = new jsPDF({
      orientation: "portrait",
      unit: "in",
      format: "letter",
    });

    const imgData = canvas.toDataURL("image/png");
    const pageWidth = pdf.internal.pageSize.getWidth();
    const pageHeight = pdf.internal.pageSize.getHeight();

    // First page
    pdf.addImage(imgData, "PNG", 0, 0, pageWidth, pageHeight);

    // Add second page with more details
    pdf.addPage();
    const secondPageContent = document.createElement("div");
    secondPageContent.style.cssText = `
      width: 8.5in;
      height: 11in;
      padding: 0.5in;
      font-family: Arial, sans-serif;
      font-size: 10px;
      line-height: 1.4;
      color: #333;
      background: white;
    `;

    const secondPageHTML = `
      <div style="text-align: center; margin-bottom: 0.2in; border-bottom: 2px solid #8B0000;">
        <h1 style="margin: 0; font-size: 14px; color: #8B0000;">${t.title} (${language === 'en' ? 'Page 2' : 'பக்கம் 2'})</h1>
      </div>

      <h3 style="margin: 0.1in 0 0.05in 0; font-size: 11px; color: #8B0000; border-bottom: 1px solid #8B0000;">${t.astrology} (${language === 'en' ? 'Continued' : 'தொடரும்'})</h3>
      <table style="width: 100%; border-collapse: collapse; margin-bottom: 0.15in;">
        <tr>
          <td style="width: 25%; padding: 4px; border: 1px solid #ccc; font-weight: bold; background: #f5f5f5; font-size: 9px;">${t.padam}:</td>
          <td style="width: 25%; padding: 4px; border: 1px solid #ccc; height: 20px;"></td>
          <td style="width: 25%; padding: 4px; border: 1px solid #ccc; font-weight: bold; background: #f5f5f5; font-size: 9px;">${t.lagnam}:</td>
          <td style="width: 25%; padding: 4px; border: 1px solid #ccc; height: 20px;"></td>
        </tr>
        <tr>
          <td colspan="2" style="padding: 4px; border: 1px solid #ccc; font-weight: bold; background: #f5f5f5; font-size: 9px;">${t.dosham}:</td>
          <td colspan="2" style="padding: 4px; border: 1px solid #ccc; height: 20px;"></td>
        </tr>
      </table>

      <h3 style="margin: 0.1in 0 0.05in 0; font-size: 11px; color: #8B0000; border-bottom: 1px solid #8B0000;">${t.expectations} (${language === 'en' ? 'Continued' : 'தொடரும்'})</h3>
      <table style="width: 100%; border-collapse: collapse; margin-bottom: 0.15in;">
        <tr>
          <td style="padding: 4px; border: 1px solid #ccc; font-weight: bold; background: #f5f5f5; font-size: 9px;">${t.partnerJob}:</td>
          <td style="padding: 4px; border: 1px solid #ccc; height: 20px;"></td>
        </tr>
        <tr>
          <td style="padding: 4px; border: 1px solid #ccc; font-weight: bold; background: #f5f5f5; font-size: 9px;">${t.partnerIncome}:</td>
          <td style="padding: 4px; border: 1px solid #ccc; height: 20px;"></td>
        </tr>
        <tr>
          <td style="padding: 4px; border: 1px solid #ccc; font-weight: bold; background: #f5f5f5; font-size: 9px;">${t.partnerDiet}:</td>
          <td style="padding: 4px; border: 1px solid #ccc; height: 20px;"></td>
        </tr>
      </table>

      <h3 style="margin: 0.1in 0 0.05in 0; font-size: 11px; color: #8B0000; border-bottom: 1px solid #8B0000;">${t.contact} (${language === 'en' ? 'Continued' : 'தொடரும்'})</h3>
      <table style="width: 100%; border-collapse: collapse; margin-bottom: 0.15in;">
        <tr>
          <td style="padding: 4px; border: 1px solid #ccc; font-weight: bold; background: #f5f5f5; font-size: 9px;">${t.address}:</td>
          <td style="padding: 4px; border: 1px solid #ccc; height: 50px; vertical-align: top;"></td>
        </tr>
      </table>

      <h3 style="margin: 0.1in 0 0.05in 0; font-size: 11px; color: #8B0000; border-bottom: 1px solid #8B0000;">${t.remarks}</h3>
      <table style="width: 100%; border-collapse: collapse;">
        <tr>
          <td style="padding: 4px; border: 1px solid #ccc; height: 80px; vertical-align: top;"></td>
        </tr>
      </table>

      <div style="margin-top: 0.3in; text-align: center; font-size: 9px; color: #999;">
        ${language === 'en' ? 'By submitting this form, you agree to our Terms and Conditions' : 'இந்த படிவத்தை சமர்ப்பிப்பதன் மூலம், நீங்கள் எங்கள் விதிமுறைகளுக்கு ஒப்புக்கொள்கிறீர்கள்'}
      </div>
    `;

    secondPageContent.innerHTML = secondPageHTML;
    document.body.appendChild(secondPageContent);

    const canvas2 = await html2canvas(secondPageContent, {
      scale: 2,
      backgroundColor: "#ffffff",
      useCORS: true,
    });

    const imgData2 = canvas2.toDataURL("image/png");
    pdf.addImage(imgData2, "PNG", 0, 0, pageWidth, pageHeight);

    // Download the PDF
    const fileName = `Matrimony_Registration_Form_${language.toUpperCase()}_${new Date().getTime()}.pdf`;
    pdf.save(fileName);

    // Cleanup
    document.body.removeChild(htmlContent);
    document.body.removeChild(secondPageContent);
  } catch (error) {
    console.error("Error generating PDF:", error);
    alert("Error generating PDF. Please try again.");
    document.body.removeChild(htmlContent);
  }
};
