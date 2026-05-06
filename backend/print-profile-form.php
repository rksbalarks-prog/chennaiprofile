<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Matrimony - Profile Registration Form</title>
<style>
@page {
  size: A4;
  margin: 8mm 10mm 8mm 10mm;
}

* { margin: 0; padding: 0; box-sizing: border-box; }

body {
  font-family: Arial, Helvetica, sans-serif;
  font-size: 11px;
  color: #000;
  background: #d0d0d0;
  line-height: 1.2;
}

/* Page */
.page {
  width: 210mm;
  min-height: 297mm;
  margin: 70px auto 20px;
  padding: 8mm 10mm 8mm 10mm;
  background: #fff;
  box-shadow: 0 2px 20px rgba(0,0,0,0.2);
}

.page + .page {
  margin-top: 20px;
}

@media print {
  body { background: #fff; margin: 0; }
  .page {
    margin: 0;
    padding: 6mm 8mm 6mm 8mm;
    box-shadow: none;
    width: 100%;
    min-height: 0;
    height: auto;
  }
  .page-1 {
    page-break-after: always;
  }
  .page-2 {
    page-break-inside: avoid;
  }
  .no-print { display: none !important; }
  .st { padding: 3px 8px; margin: 5px 0 0 0; font-size: 11px; }
  .l { padding: 4px 6px; font-size: 9px; min-width: 100px; max-width: 120px; }
  .v { padding: 4px 6px; min-height: 22px; font-size: 10px; }
  .rf .v { min-height: 22px; }
  .r, .rf { border-bottom-width: 1px; }
  .f { border-right-width: 1px; }
  .bs { border-width: 1.5px; }
  .form-header { padding: 6px 8px; margin-bottom: 4px; }
  .form-header h1 { font-size: 17px; }
  .form-header p { font-size: 10px; }
  .reg-row { margin-bottom: 5px; }
  .reg-label { padding: 4px 6px; font-size: 9px; }
  .reg-val { padding: 4px 6px; min-height: 20px; }
  .decl { margin-top: 6px; padding: 5px 8px; font-size: 9px; }
  .sig-row { margin-top: 10px; }
  .sig-line { margin-top: 22px; }
  .sib th, .sib td { padding: 4px 6px; }
}

/* Action bar */
.no-print {
  position: fixed;
  top: 0; left: 0; right: 0;
  z-index: 999;
  background: linear-gradient(135deg, #1a1a2e, #2d2d5e);
  padding: 12px 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 14px;
  box-shadow: 0 3px 16px rgba(0,0,0,0.25);
}

.no-print .bar-title {
  color: #fff;
  font-size: 15px;
  font-weight: 600;
  margin-right: 20px;
}

.action-btn {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  padding: 9px 22px;
  font-size: 13.5px;
  font-weight: 600;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
  font-family: Arial, sans-serif;
}

.action-btn svg { width: 18px; height: 18px; }
.btn-print { background: #fff; color: #1a1a2e; }
.btn-print:hover { background: #f0ede8; }
.btn-download { background: #e8624a; color: #fff; }
.btn-download:hover { background: #d4553f; }

/* Header */
.form-header {
  text-align: center;
  border: 2px solid #000;
  padding: 8px 10px;
  margin-bottom: 6px;
  background: #000;
  color: #fff;
}

.form-header h1 {
  font-size: 20px;
  font-weight: 900;
  letter-spacing: 2px;
  text-transform: uppercase;
}

.form-header p {
  font-size: 11px;
  margin-top: 2px;
  letter-spacing: 1px;
  font-weight: 600;
}

/* Page number */
.page-num {
  text-align: right;
  font-size: 9px;
  font-weight: 700;
  color: #000;
  margin-bottom: 4px;
}

/* Registration row */
.reg-row {
  display: flex;
  border: 2px solid #000;
  margin-bottom: 8px;
}

.reg-field {
  display: flex;
  align-items: center;
  flex: 1;
}

.reg-field + .reg-field { border-left: 2px solid #000; }

.reg-label {
  background: #000;
  color: #fff;
  font-weight: 800;
  font-size: 10px;
  padding: 6px 8px;
  text-transform: uppercase;
  white-space: nowrap;
  letter-spacing: 0.5px;
}

.reg-val {
  flex: 1;
  padding: 6px 8px;
  min-height: 24px;
  font-size: 11px;
}

/* Section title */
.st {
  background: #000;
  color: #fff;
  font-size: 12px;
  font-weight: 900;
  padding: 5px 10px;
  margin: 8px 0 0 0;
  letter-spacing: 1px;
  text-transform: uppercase;
  border: 2px solid #000;
  border-bottom: none;
}

/* Bordered section */
.bs {
  border: 2px solid #000;
  margin-bottom: 0;
}

/* Row */
.r {
  display: flex;
  border-bottom: 1.5px solid #000;
}

.r:last-child { border-bottom: none; }

/* Field */
.f {
  display: flex;
  align-items: stretch;
  flex: 1;
  border-right: 1.5px solid #000;
  min-width: 0;
}

.f:last-child { border-right: none; }

/* Label */
.l {
  background: #e8e8e8;
  font-weight: 800;
  font-size: 10px;
  color: #000;
  padding: 5px 8px;
  min-width: 110px;
  max-width: 130px;
  display: flex;
  align-items: center;
  border-right: 1.5px solid #000;
  text-transform: uppercase;
  letter-spacing: 0.3px;
  line-height: 1.3;
}

/* Value */
.v {
  flex: 1;
  padding: 5px 8px;
  min-height: 26px;
  font-size: 11px;
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 1px 4px;
}

/* Full width row */
.rf {
  display: flex;
  align-items: stretch;
  border-bottom: 1.5px solid #000;
}

.rf:last-child { border-bottom: none; }
.rf .l { min-width: 110px; max-width: 130px; }
.rf .v { min-height: 26px; }

/* Checkbox */
.o {
  width: 12px;
  height: 12px;
  border: 2px solid #000;
  display: inline-block;
  margin-right: 2px;
  vertical-align: middle;
  border-radius: 2px;
  flex-shrink: 0;
}

/* Options text */
.opts {
  font-size: 10.5px;
  font-weight: 600;
  display: flex;
  flex-wrap: wrap;
  gap: 2px 10px;
  align-items: center;
}

/* Sibling table */
.sib {
  width: 100%;
  border-collapse: collapse;
  font-size: 10.5px;
  border: 2px solid #000;
}

.sib th, .sib td {
  border: 1.5px solid #000;
  padding: 5px 8px;
  text-align: center;
}

.sib th {
  background: #e8e8e8;
  font-weight: 800;
  font-size: 10px;
  text-transform: uppercase;
  color: #000;
}

.sib td { min-height: 22px; }

/* Declaration */
.decl {
  font-size: 10px;
  color: #000;
  margin-top: 10px;
  padding: 8px 10px;
  border: 2px solid #000;
  line-height: 1.5;
  font-weight: 500;
}

.sig-row {
  display: flex;
  justify-content: space-between;
  margin-top: 15px;
  padding: 0 15px;
}

.sig-block {
  text-align: center;
  font-size: 10px;
  color: #000;
  font-weight: 700;
}

.sig-line {
  width: 150px;
  border-top: 2px solid #000;
  margin-top: 28px;
  padding-top: 4px;
}

/* Larger address fields */
.addr-val { min-height: 34px !important; }

/* Wide label for some fields */
.l-wide { min-width: 130px; max-width: 150px; }
</style>
</head>
<body>

<div class="no-print">
  <span class="bar-title">Registration Form</span>
  <button class="action-btn btn-print" onclick="window.print()">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
    Print
  </button>
  <button class="action-btn btn-download" onclick="downloadPDF()">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
    Download PDF
  </button>
</div>

<!-- ======================== PAGE 1 ======================== -->
<div class="page page-1" id="page1">

  <!-- HEADER -->
  <div class="form-header">
    <h1>Chennai Profile Matrimony</h1>
    <p>Profile Registration Form</p>
  </div>

  <div class="page-num">Page 1 of 2</div>

  <!-- Reg & Date & Gender -->
  <div class="reg-row">
    <div class="reg-field"><div class="reg-label">Reg. No</div><div class="reg-val"></div></div>
    <div class="reg-field"><div class="reg-label">Date</div><div class="reg-val"></div></div>
    <div class="reg-field"><div class="reg-label">Gender</div><div class="reg-val opts"><span class="o"></span> Male &nbsp;&nbsp;<span class="o"></span> Female</div></div>
  </div>

  <!-- 1. PERSONAL DETAILS -->
  <div class="st">1. Personal Details</div>
  <div class="bs">
    <div class="r">
      <div class="f"><div class="l">Mobile *</div><div class="v"></div></div>
      <div class="f"><div class="l">Name *</div><div class="v"></div></div>
    </div>
    <div class="r">
      <div class="f"><div class="l">Date of Birth *</div><div class="v"></div></div>
      <div class="f"><div class="l">Age</div><div class="v"></div></div>
    </div>
    <div class="r">
      <div class="f" style="flex:1"><div class="l">Marital Status *</div><div class="v opts"><span class="o"></span> Unmarried &nbsp;&nbsp;<span class="o"></span> Divorced &nbsp;&nbsp;<span class="o"></span> Widowed &nbsp;&nbsp;<span class="o"></span> Separated</div></div>
    </div>
    <div class="r">
      <div class="f" style="flex:1"><div class="l">Religion *</div><div class="v opts"><span class="o"></span> Hindu &nbsp;&nbsp;<span class="o"></span> Muslim &nbsp;&nbsp;<span class="o"></span> Christian &nbsp;&nbsp;<span class="o"></span> Sikh &nbsp;&nbsp;<span class="o"></span> Jain &nbsp;&nbsp;<span class="o"></span> Buddhist</div></div>
    </div>
    <div class="r">
      <div class="f"><div class="l">Caste *</div><div class="v"></div></div>
      <div class="f"><div class="l">Sub Caste</div><div class="v"></div></div>
    </div>
    <div class="r">
      <div class="f"><div class="l">Mother Tongue *</div><div class="v"></div></div>
      <div class="f"><div class="l">Nationality</div><div class="v"></div></div>
    </div>
    <div class="r">
      <div class="f"><div class="l">Own House</div><div class="v opts"><span class="o"></span> Yes &nbsp;&nbsp;<span class="o"></span> No</div></div>
      <div class="f"><div class="l">Born As</div><div class="v">________ Son / Daughter</div></div>
    </div>
    <div class="r">
      <div class="f" style="flex:1"><div class="l">Birth Time</div><div class="v">Hour: ________ &nbsp;&nbsp; Min: ________ &nbsp;&nbsp; <span class="o"></span> AM &nbsp;&nbsp;<span class="o"></span> PM</div></div>
    </div>
    <div class="r">
      <div class="f"><div class="l">Place of Birth</div><div class="v"></div></div>
      <div class="f"><div class="l">Nativity</div><div class="v"></div></div>
    </div>
    <div class="r">
      <div class="f" style="flex:1"><div class="l">Present Country</div><div class="v"></div></div>
    </div>
    <div class="rf">
      <div class="l">Additional Details</div>
      <div class="v" style="min-height:36px"></div>
    </div>
  </div>

  <!-- 2. FAMILY DETAILS -->
  <div class="st">2. Family Details</div>
  <div class="bs">
    <div class="r">
      <div class="f"><div class="l">Father's Name</div><div class="v"></div></div>
      <div class="f"><div class="l">Occupation</div><div class="v"></div></div>
    </div>
    <div class="r">
      <div class="f" style="flex:1"><div class="l">Father Status</div><div class="v opts"><span class="o"></span> Employed &nbsp;&nbsp;<span class="o"></span> Businessman &nbsp;&nbsp;<span class="o"></span> Professional &nbsp;&nbsp;<span class="o"></span> Retired &nbsp;&nbsp;<span class="o"></span> Not Employed &nbsp;&nbsp;<span class="o"></span> Passed Away</div></div>
    </div>
    <div class="r">
      <div class="f"><div class="l">Mother's Name</div><div class="v"></div></div>
      <div class="f"><div class="l">Occupation</div><div class="v"></div></div>
    </div>
    <div class="r">
      <div class="f" style="flex:1"><div class="l">Mother Status</div><div class="v opts"><span class="o"></span> Home Maker &nbsp;&nbsp;<span class="o"></span> Employed &nbsp;&nbsp;<span class="o"></span> Businessman &nbsp;&nbsp;<span class="o"></span> Retired &nbsp;&nbsp;<span class="o"></span> Not Employed &nbsp;&nbsp;<span class="o"></span> Passed Away</div></div>
    </div>
  </div>

  <!-- Siblings -->
  <table class="sib" style="margin-top:0;border-top:none">
    <thead>
      <tr><th style="width:100px">Siblings</th><th>Elder Brother</th><th>Younger Brother</th><th>Elder Sister</th><th>Younger Sister</th></tr>
    </thead>
    <tbody>
      <tr><td style="font-weight:800;background:#e8e8e8">Married</td><td style="height:28px"></td><td></td><td></td><td></td></tr>
      <tr><td style="font-weight:800;background:#e8e8e8">Unmarried</td><td style="height:28px"></td><td></td><td></td><td></td></tr>
    </tbody>
  </table>

  <!-- 3. PHYSICAL ATTRIBUTES -->
  <div class="st">3. Physical Attributes</div>
  <div class="bs">
    <div class="r">
      <div class="f"><div class="l">Height</div><div class="v"></div></div>
      <div class="f"><div class="l">Weight</div><div class="v"></div></div>
      <div class="f"><div class="l">Blood Group</div><div class="v"></div></div>
    </div>
    <div class="r">
      <div class="f"><div class="l">Diet</div><div class="v opts"><span class="o"></span> Vegetarian &nbsp;&nbsp;<span class="o"></span> Non-Vegetarian &nbsp;&nbsp;<span class="o"></span> Eggetarian</div></div>
      <div class="f"><div class="l">Complexion</div><div class="v"></div></div>
    </div>
    <div class="r">
      <div class="f" style="flex:1"><div class="l">Disability</div><div class="v opts"><span class="o"></span> No &nbsp;&nbsp;<span class="o"></span> Yes &nbsp;&nbsp;&nbsp;&nbsp; If Yes, specify: ________________________________</div></div>
    </div>
  </div>

  <!-- 4. EDUCATION & OCCUPATION -->
  <div class="st">4. Education & Occupation</div>
  <div class="bs">
    <div class="r">
      <div class="f" style="flex:1"><div class="l">Qualification</div><div class="v"></div></div>
    </div>
    <div class="r">
      <div class="f" style="flex:1"><div class="l">Occupation</div><div class="v"></div></div>
    </div>
    <div class="r">
      <div class="f"><div class="l">Place of Work</div><div class="v"></div></div>
      <div class="f"><div class="l">Monthly Income</div><div class="v">Rs.</div></div>
    </div>
  </div>


</div>

<!-- ======================== PAGE 2 ======================== -->
<div class="page page-2" id="page2">

  <!-- HEADER (repeated) -->
  <div class="form-header">
    <h1>Chennai Profile Matrimony</h1>
    <p>Profile Registration Form</p>
  </div>

  <div class="page-num">Page 2 of 2</div>

  <!-- Reg No repeat -->
  <div class="reg-row">
    <div class="reg-field"><div class="reg-label">Reg. No</div><div class="reg-val"></div></div>
    <div class="reg-field"><div class="reg-label">Name</div><div class="reg-val"></div></div>
    <div class="reg-field"><div class="reg-label">Mobile</div><div class="reg-val"></div></div>
  </div>

  <!-- 5. ASTROLOGY -->
  <div class="st">5. Astrology</div>
  <div class="bs">
    <div class="r">
      <div class="f"><div class="l">Star</div><div class="v"></div></div>
      <div class="f"><div class="l">Raasi</div><div class="v"></div></div>
      <div class="f"><div class="l">Padam</div><div class="v"></div></div>
      <div class="f"><div class="l">Laknam</div><div class="v"></div></div>
    </div>
    <div class="r">
      <div class="f"><div class="l">Gothram</div><div class="v"></div></div>
      <div class="f"><div class="l">Dosham</div><div class="v opts"><span class="o"></span> No &nbsp;&nbsp;<span class="o"></span> Yes &nbsp;&nbsp;<span class="o"></span> Partial</div></div>
      <div class="f"><div class="l">Dosham Type</div><div class="v"></div></div>
    </div>
  </div>

  <!-- 6. PARTNER EXPECTATIONS -->
  <div class="st">6. Partner Expectations</div>
  <div class="bs">
    <div class="r">
      <div class="f"><div class="l">Qualification</div><div class="v"></div></div>
      <div class="f"><div class="l">Job Preference</div><div class="v"></div></div>
      <div class="f"><div class="l">Job Req.</div><div class="v opts"><span class="o"></span> Optional <span class="o"></span> Must <span class="o"></span> Not Req.</div></div>
    </div>
    <div class="r">
      <div class="f"><div class="l">Income Exp.</div><div class="v">Rs.</div></div>
      <div class="f"><div class="l">Age From</div><div class="v"></div></div>
      <div class="f"><div class="l">Age To</div><div class="v"></div></div>
    </div>
    <div class="r">
      <div class="f"><div class="l">Diet</div><div class="v opts"><span class="o"></span> Veg &nbsp;<span class="o"></span> Non-Veg &nbsp;<span class="o"></span> Any</div></div>
      <div class="f"><div class="l">Marital Status</div><div class="v opts"><span class="o"></span> Unmarried <span class="o"></span> Divorced <span class="o"></span> Widowed <span class="o"></span> Any</div></div>
    </div>
    <div class="r">
      <div class="f"><div class="l">Horoscope Req?</div><div class="v opts"><span class="o"></span> No &nbsp;&nbsp;<span class="o"></span> Yes &nbsp;&nbsp;<span class="o"></span> Not Applicable</div></div>
      <div class="f"><div class="l">Caste Pref.</div><div class="v"></div></div>
    </div>
    <div class="rf">
      <div class="l">Sub Caste Pref.</div>
      <div class="v" style="min-height:26px"></div>
    </div>
    <div class="rf">
      <div class="l">Other Requirements</div>
      <div class="v" style="min-height:30px"></div>
    </div>
  </div>

  <!-- 7. COMMUNICATION DETAILS -->
  <div class="st">7. Communication Details</div>
  <div class="bs">
    <div class="rf">
      <div class="l">Permanent Addr.</div>
      <div class="v" style="min-height:32px"></div>
    </div>
    <div class="rf">
      <div class="l">Present Addr.</div>
      <div class="v" style="min-height:32px"></div>
    </div>
    <div class="r">
      <div class="f"><div class="l">Area</div><div class="v"></div></div>
      <div class="f"><div class="l">City</div><div class="v"></div></div>
      <div class="f"><div class="l">District</div><div class="v"></div></div>
      <div class="f"><div class="l">State</div><div class="v"></div></div>
    </div>
    <div class="r">
      <div class="f"><div class="l">Contact Person</div><div class="v"></div></div>
      <div class="f"><div class="l">Contact No.</div><div class="v"></div></div>
      <div class="f"><div class="l">Email</div><div class="v"></div></div>
    </div>
  </div>

  <!-- DECLARATION -->
  <div class="decl">
    <strong>Declaration:</strong> I hereby declare that all the information provided above is true and correct to the best of my knowledge. I understand that any false or misleading information may result in cancellation of my registration.
  </div>

  <div class="sig-row">
    <div class="sig-block"><div class="sig-line">Date</div></div>
    <div class="sig-block"><div class="sig-line">Applicant's Signature</div></div>
    <div class="sig-block"><div class="sig-line">Office Use</div></div>
  </div>

  <!-- 8. OFFICE INFO (Office Use Only) -->
  <div class="st" style="margin-top:10px">8. Office Info <span style="font-size:8px;font-weight:400;letter-spacing:0;text-transform:none">(For Office Use Only)</span></div>
  <div class="bs">
    <div class="r">
      <div class="f"><div class="l">Data By</div><div class="v opts"><span class="o"></span> User &nbsp;&nbsp;<span class="o"></span> Office</div></div>
      <div class="f"><div class="l">Name &amp; Role</div><div class="v"></div></div>
    </div>
    <div class="r">
      <div class="f" style="flex:1"><div class="l">Mandatory</div><div class="v opts"><span class="o"></span> Yes &nbsp;&nbsp;<span class="o"></span> No</div></div>
    </div>
    <div class="r">
      <div class="f"><div class="l">Approved By</div><div class="v"></div></div>
      <div class="f"><div class="l">Approved Date</div><div class="v"></div></div>
    </div>
    <div class="r">
      <div class="f"><div class="l">Bill Number</div><div class="v"></div></div>
      <div class="f"><div class="l">Plan Name</div><div class="v"></div></div>
    </div>
    <div class="r">
      <div class="f"><div class="l">Bill Amount</div><div class="v">Rs.</div></div>
      <div class="f"><div class="l">Payment Type</div><div class="v"></div></div>
    </div>
    <div class="r">
      <div class="f"><div class="l">Bill Date</div><div class="v"></div></div>
      <div class="f"><div class="l">Billed By</div><div class="v"></div></div>
    </div>
  </div>

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
function downloadPDF() {
  const btn = document.querySelector('.btn-download');
  btn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg> Generating...';
  btn.disabled = true;

  const pages = document.querySelectorAll('.page');
  const opt = {
    margin:       [8, 10, 8, 10],
    filename:     'Matrimony_Registration_Form.pdf',
    image:        { type: 'jpeg', quality: 0.98 },
    html2canvas:  { scale: 2, useCORS: true, letterRendering: true },
    jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' },
    pagebreak:    { mode: ['css'], before: '.page+.page' }
  };

  // Wrap both pages in a temp container
  const wrapper = document.createElement('div');
  pages.forEach(p => wrapper.appendChild(p.cloneNode(true)));

  html2pdf().set(opt).from(wrapper).save().then(function() {
    btn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg> Download PDF';
    btn.disabled = false;
  });
}
</script>
</body>
</html>
