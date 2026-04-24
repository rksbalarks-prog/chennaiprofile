import React, { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { useNavigate } from 'react-router-dom';
import './GoogleFormStyle.css';

export default function GoogleForm() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const [selectedPhotos, setSelectedPhotos] = useState([]);
  const [rasiChart, setRasiChart] = useState(null);
  const [formData, setFormData] = useState({});

  const handleInputChange = (e) => {
    const { name, value, type, checked } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: type === 'checkbox' ? checked : value
    }));
  };

  const handleMultiplePhotoUpload = (event) => {
    const files = Array.from(event.target.files);
    const totalPhotos = selectedPhotos.length + files.length;

    if (totalPhotos > 2) {
      alert('Maximum 2 photos allowed');
      event.target.value = '';
      return;
    }

    files.forEach(file => {
      if (file.size > 30 * 1024 * 1024) {
        alert('File size must be less than 30MB');
        event.target.value = '';
        return;
      }
      if (!file.type.startsWith('image/')) {
        alert('Please select a valid image file');
        event.target.value = '';
        return;
      }

      const reader = new FileReader();
      reader.onload = (e) => {
        setSelectedPhotos(prev => [...prev, {
          id: Date.now(),
          src: e.target.result,
          file: file
        }]);
      };
      reader.readAsDataURL(file);
    });

    event.target.value = '';
  };

  const removePhotoByIndex = (photoId) => {
    setSelectedPhotos(prev => prev.filter(p => p.id !== photoId));
  };

  const handleRasiUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
      if (file.size > 5 * 1024 * 1024) {
        alert('File size must be less than 5MB');
        event.target.value = '';
        return;
      }
      if (!file.type.startsWith('image/')) {
        alert('Please select a valid image file');
        event.target.value = '';
        return;
      }

      const reader = new FileReader();
      reader.onload = (e) => {
        setRasiChart({ src: e.target.result, file: file });
      };
      reader.readAsDataURL(file);
    }
  };

  const removeRasiChart = () => {
    setRasiChart(null);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    const formDataToSend = new FormData();
    
    // Append all form data
    Object.entries(formData).forEach(([key, value]) => {
      formDataToSend.append(key, value);
    });
    
    // Append selected photos
    selectedPhotos.forEach((photo, index) => {
      formDataToSend.append(`photo_${index}`, photo.file);
    });
    
    // Append rasi chart
    if (rasiChart) {
      formDataToSend.append('rasiChart', rasiChart.file);
    }

    try {
      const response = await fetch('/API/register.php', {
        method: 'POST',
        body: formDataToSend
      });
      
      if (response.ok) {
        alert('Profile registered successfully!');
        e.target.reset();
        setSelectedPhotos([]);
        setRasiChart(null);
        setFormData({});
      } else {
        alert('Failed to register profile. Please try again.');
      }
    } catch (error) {
      console.error('Error:', error);
      alert('Error submitting form. Please try again.');
    }
  };

  const FormSection = ({ title, children }) => (
    <div className="section-card">
      <div className="section-title">{title}</div>
      {children}
    </div>
  );

  const Question = ({ label, required, children }) => (
    <div className="question">
      <label className="q-label">
        {label} {required && <span className="req">*</span>}
      </label>
      {children}
    </div>
  );

  return (
    <div className="form-container">
      {/* Header */}
      <div className="form-header" style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', gap: '24px' }}>
        <div style={{ flex: 1 }}>
          <h1>CHENNAI PROFILES</h1>
          <div className="subtitle">Register your profile</div>
          <div className="notice">
            <strong>Profile Registration Form</strong><br />
            Fill all mandatory fields marked with <strong style={{ color: '#d93025' }}>*</strong><br />
            Your information will be kept confidential and secure.
          </div>
          <div className="required-note">* Required fields</div>
        </div>
        <div style={{ textAlign: 'right', paddingTop: '8px' }}>
          <div style={{ fontSize: '14px', fontWeight: '500', marginBottom: '12px', color: '#202124' }}>
            மொழியை மாற்று
          </div>
          <div style={{ display: 'flex', flexDirection: 'column', gap: '8px', minWidth: '120px' }}>
            <button 
              onClick={() => navigate('/google-form-ta')}
              style={{ padding: '8px 16px', border: '1px solid #dadce0', backgroundColor: '#fff', borderRadius: '4px', cursor: 'pointer', fontSize: '13px', fontWeight: '500', color: '#1f2937', transition: 'all 0.2s' }}
              onMouseEnter={(e) => e.target.style.backgroundColor = '#f8f9fa'}
              onMouseLeave={(e) => e.target.style.backgroundColor = '#fff'}
            >
              தமிழ்
            </button>
          </div>
        </div>
      </div>

      <form id="matrimonyForm" onSubmit={handleSubmit}>
        {/* 1. Personal & Family Details */}
        <FormSection title="1. Personal & Family Details">
          <Question label="Full Name" required>
            <input type="text" name="name" placeholder="Your answer" onChange={handleInputChange} required />
          </Question>

          <Question label="Gender" required>
            <div className="radio-group">
              <label><input type="radio" name="gender" value="Male" onChange={handleInputChange} required /> Male</label>
              <label><input type="radio" name="gender" value="Female" onChange={handleInputChange} /> Female</label>
            </div>
          </Question>

          <div className="two-col">
            <Question label="Date of Birth" required>
              <input type="date" name="dob" onChange={handleInputChange} required />
            </Question>
            <Question label="Time of Birth">
              <div className="time-group">
                <select name="birthHour" onChange={handleInputChange} style={{ width: 'auto' }}>
                  <option value="">Hr</option>
                  {Array.from({ length: 24 }, (_, i) => (
                    <option key={i} value={String(i).padStart(2, '0')}>
                      {String(i).padStart(2, '0')}
                    </option>
                  ))}
                </select>
                <select name="birthMin" onChange={handleInputChange} style={{ width: 'auto' }}>
                  <option value="">Min</option>
                  <option value="00">00</option>
                  <option value="15">15</option>
                  <option value="30">30</option>
                  <option value="45">45</option>
                </select>
                <select name="birthAmPm" onChange={handleInputChange} style={{ width: 'auto' }}>
                  <option value="AM">AM</option>
                  <option value="PM">PM</option>
                </select>
              </div>
            </Question>
          </div>

          <div className="two-col">
            <Question label="Place of Birth" required>
              <input type="text" name="placeBirth" placeholder="City/Town name" onChange={handleInputChange} required />
            </Question>
            <Question label="Nativity (State)" required>
              <input type="text" name="nativity" placeholder="Your native state" onChange={handleInputChange} required />
            </Question>
          </div>

          <div className="two-col">
            <Question label="Mother Tongue" required>
              <select name="motherTongue" onChange={handleInputChange} required>
                <option value="">Select</option>
                <option value="Tamil">Tamil</option>
                <option value="Telugu">Telugu</option>
                <option value="Kannada">Kannada</option>
                <option value="Malayalam">Malayalam</option>
                <option value="Hindi">Hindi</option>
                <option value="Marathi">Marathi</option>
                <option value="English">English</option>
              </select>
            </Question>
            <Question label="Marital Status" required>
              <div className="radio-group">
                <label><input type="radio" name="maritalStatus" value="Unmarried" onChange={handleInputChange} required /> Unmarried</label>
                <label><input type="radio" name="maritalStatus" value="Married" onChange={handleInputChange} /> Married</label>
                <label><input type="radio" name="maritalStatus" value="Divorced" onChange={handleInputChange} /> Divorced</label>
                <label><input type="radio" name="maritalStatus" value="Widowed" onChange={handleInputChange} /> Widowed</label>
              </div>
            </Question>
          </div>

          <div className="two-col">
            <Question label="Father's Name">
              <input type="text" name="fatherName" placeholder="Father's full name" onChange={handleInputChange} />
            </Question>
            <Question label="Father's Status">
              <div className="radio-group">
                <label><input type="radio" name="fatherAlive" value="yes" onChange={handleInputChange} /> Alive</label>
                <label><input type="radio" name="fatherAlive" value="no" onChange={handleInputChange} /> No more</label>
              </div>
            </Question>
          </div>

          <Question label="Father's Occupation">
            <input type="text" name="fatherJob" placeholder="Father's occupation" onChange={handleInputChange} />
          </Question>

          <div className="two-col">
            <Question label="Mother's Name">
              <input type="text" name="motherName" placeholder="Mother's full name" onChange={handleInputChange} />
            </Question>
            <Question label="Mother's Status">
              <div className="radio-group">
                <label><input type="radio" name="motherAlive" value="yes" onChange={handleInputChange} /> Alive</label>
                <label><input type="radio" name="motherAlive" value="no" onChange={handleInputChange} /> No more</label>
              </div>
            </Question>
          </div>

          <Question label="Mother's Occupation">
            <input type="text" name="motherJob" placeholder="Mother's occupation" onChange={handleInputChange} />
          </Question>

          <Question label="Siblings">
            <table className="sibling-table">
              <thead>
                <tr>
                  <th>Status</th>
                  <th>Elder Brother</th>
                  <th>Younger Brother</th>
                  <th>Elder Sister</th>
                  <th>Younger Sister</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><strong>Married</strong></td>
                  <td><select name="sibMarriedEB" onChange={handleInputChange}><option>-</option><option>0</option><option>1</option><option>2</option><option>3</option><option>4</option><option>5+</option></select></td>
                  <td><select name="sibMarriedYB" onChange={handleInputChange}><option>-</option><option>0</option><option>1</option><option>2</option><option>3</option><option>4</option><option>5+</option></select></td>
                  <td><select name="sibMarriedES" onChange={handleInputChange}><option>-</option><option>0</option><option>1</option><option>2</option><option>3</option><option>4</option><option>5+</option></select></td>
                  <td><select name="sibMarriedYS" onChange={handleInputChange}><option>-</option><option>0</option><option>1</option><option>2</option><option>3</option><option>4</option><option>5+</option></select></td>
                </tr>
                <tr>
                  <td><strong>Unmarried</strong></td>
                  <td><select name="sibUnmarriedEB" onChange={handleInputChange}><option>-</option><option>0</option><option>1</option><option>2</option><option>3</option><option>4</option><option>5+</option></select></td>
                  <td><select name="sibUnmarriedYB" onChange={handleInputChange}><option>-</option><option>0</option><option>1</option><option>2</option><option>3</option><option>4</option><option>5+</option></select></td>
                  <td><select name="sibUnmarriedES" onChange={handleInputChange}><option>-</option><option>0</option><option>1</option><option>2</option><option>3</option><option>4</option><option>5+</option></select></td>
                  <td><select name="sibUnmarriedYS" onChange={handleInputChange}><option>-</option><option>0</option><option>1</option><option>2</option><option>3</option><option>4</option><option>5+</option></select></td>
                </tr>
              </tbody>
            </table>
          </Question>

          <Question label="Additional Details">
            <textarea name="others" placeholder="Talents, Achievements, Likes, Visa Status, Family info, etc." onChange={handleInputChange}></textarea>
          </Question>

          <Question label="Upload Your Photos">
            <p style={{ fontSize: '12px', color: '#5f6368', marginBottom: '12px' }}>Upload up to 2 photos</p>
            <div className="photo-upload-box" style={{ cursor: 'pointer', transition: 'all 0.2s' }}>
              <div style={{ fontSize: '32px', marginBottom: '8px' }}>📷</div>
              <p style={{ fontWeight: '500', marginBottom: '4px' }}>Select Your Photos</p>
              <input type="file" name="photos" accept="image/*" multiple onChange={handleMultiplePhotoUpload} style={{ fontSize: '13px', color: '#7c3aed', cursor: 'pointer' }} />
            </div>
            <div id="photosPreviews" style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(150px, 1fr))', gap: '12px', marginTop: '16px' }}>
              {selectedPhotos.map(photo => (
                <div key={photo.id} style={{ position: 'relative' }}>
                  <img src={photo.src} alt="Preview" style={{ width: '100%', borderRadius: '6px', border: '1px solid #e8d5fa', height: '150px', objectFit: 'cover' }} />
                  <button type="button" onClick={() => removePhotoByIndex(photo.id)} style={{ position: 'absolute', top: '4px', right: '4px', background: '#d93025', color: 'white', border: 'none', padding: '4px 8px', borderRadius: '4px', cursor: 'pointer', fontSize: '12px' }}>✕</button>
                </div>
              ))}
            </div>
          </Question>
        </FormSection>

        {/* 2. Physical Attributes */}
        <FormSection title="2. Physical Attributes">
          <div className="two-col">
            <Question label="Height">
              <select name="height" onChange={handleInputChange}>
                <option value="">Select</option>
                {['4\'8"', '4\'9"', '4\'10"', '4\'11"', '5\'0"', '5\'1"', '5\'2"', '5\'3"', '5\'4"', '5\'5"', '5\'6"', '5\'7"', '5\'8"', '5\'9"', '5\'10"', '5\'11"', '6\'0"'].map(h => (
                  <option key={h} value={h}>{h}</option>
                ))}
              </select>
            </Question>
            <Question label="Weight">
              <select name="weight" onChange={handleInputChange}>
                <option value="">Select (kg)</option>
                {[40, 45, 50, 55, 60, 65, 70, 75, 80, 85, 90].map(w => (
                  <option key={w} value={`${w}kg`}>{w}kg</option>
                ))}
              </select>
            </Question>
          </div>

          <div className="two-col">
            <Question label="Blood Group">
              <select name="bloodGroup" onChange={handleInputChange}>
                <option value="">Select</option>
                {['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-'].map(bg => (
                  <option key={bg} value={bg}>{bg}</option>
                ))}
              </select>
            </Question>
            <Question label="Complexion">
              <select name="complexion" onChange={handleInputChange}>
                <option value="">Select</option>
                <option value="Very Fair">Very Fair</option>
                <option value="Fair">Fair</option>
                <option value="Wheatish">Wheatish</option>
                <option value="Wheatish Brown">Wheatish Brown</option>
                <option value="Dark">Dark</option>
              </select>
            </Question>
          </div>

          <div className="two-col">
            <Question label="Diet">
              <div className="radio-group">
                <label><input type="radio" name="diet" value="Vegetarian" onChange={handleInputChange} /> Vegetarian</label>
                <label><input type="radio" name="diet" value="Non-Vegetarian" onChange={handleInputChange} /> Non-Vegetarian</label>
                <label><input type="radio" name="diet" value="Eggetarian" onChange={handleInputChange} /> Eggetarian</label>
              </div>
            </Question>
            <Question label="Disability (if any)">
              <div className="radio-group">
                <label><input type="radio" name="disability" value="No" onChange={handleInputChange} /> No</label>
                <label><input type="radio" name="disability" value="Yes" onChange={handleInputChange} /> Yes</label>
              </div>
            </Question>
          </div>
        </FormSection>

        {/* 3. Education & Occupation */}
        <FormSection title="3. Education & Occupation Details">
          <div className="two-col">
            <Question label="Qualification">
              <input type="text" name="qualification" placeholder="e.g. B.E., M.Sc., MBA" onChange={handleInputChange} />
            </Question>
            <Question label="Job / Profession">
              <input type="text" name="job" placeholder="Your answer" onChange={handleInputChange} />
            </Question>
          </div>

          <div className="two-col">
            <Question label="Place of Work">
              <input type="text" name="placeJob" placeholder="Your answer" onChange={handleInputChange} />
            </Question>
            <Question label="Monthly Income (₹)">
              <input type="text" name="incomeMonth" placeholder="e.g. 25000" onChange={handleInputChange} />
            </Question>
          </div>
        </FormSection>

        {/* 4. Astrology Details */}
        <FormSection title="4. Astrology Details">
          <div className="two-col">
            <Question label="Caste">
              <select name="caste" onChange={handleInputChange}>
                <option value="">Select</option>
                {['Any', 'Brahmin', 'Kshatriya', 'Vellalar', 'Nadar', 'Mudaliar', 'Pillai', 'Gounder', 'Naicker', 'Chettiar', 'Vishwakarma', 'Yadav', 'Vanniyar', 'Thevar', 'Agamudayar', 'Others'].map(c => (
                  <option key={c} value={c}>{c}</option>
                ))}
              </select>
            </Question>
            <Question label="Sub Caste">
              <input type="text" name="subCaste" placeholder="Sub-caste name" onChange={handleInputChange} />
            </Question>
          </div>

          <Question label="Gothram">
            <input type="text" name="gothram" placeholder="Your answer" onChange={handleInputChange} />
          </Question>

          <div className="three-col">
            <Question label="Star (Nakshatra)">
              <select name="star" onChange={handleInputChange}>
                <option value="">Select Star</option>
                {['Ashwini', 'Bharani', 'Krittika', 'Rohini', 'Mrigashirsha', 'Ardra', 'Punarvasu', 'Pushya', 'Ashlesha', 'Magha', 'Purva Phalguni', 'Uttara Phalguni', 'Hasta', 'Chitra', 'Swati', 'Vishakha', 'Anuradha', 'Jyeshtha', 'Moola', 'Purva Ashadha', 'Uttara Ashadha', 'Shravana', 'Dhanishta', 'Shatabhisha', 'Purva Bhadrapada', 'Uttara Bhadrapada', 'Revati'].map(s => (
                  <option key={s} value={s}>{s}</option>
                ))}
              </select>
            </Question>
            <Question label="Raasi / Moon Sign">
              <select name="raasi" onChange={handleInputChange}>
                <option value="">Select Raasi</option>
                {['Mesham', 'Rishabam', 'Mithunam', 'Kadagam', 'Simmam', 'Kanni', 'Thulam', 'Viruchigam', 'Dhanusu', 'Magaram', 'Kumbam', 'Meenam'].map(r => (
                  <option key={r} value={r}>{r}</option>
                ))}
              </select>
            </Question>
            <Question label="Padam">
              <select name="padam" onChange={handleInputChange}>
                <option value="">Select</option>
                {[1, 2, 3, 4].map(p => (
                  <option key={p} value={`Padam ${p}`}>{p}</option>
                ))}
              </select>
            </Question>
          </div>

          <Question label="Lagnam">
            <select name="laknam" onChange={handleInputChange}>
              <option value="">Select Lagnam</option>
              {['Mesha', 'Rishaba', 'Mithuna', 'Kataka', 'Simha', 'Kanya', 'Tula', 'Vrischika', 'Dhanu', 'Makara', 'Kumbha', 'Meena'].map(l => (
                <option key={l} value={l}>{l}</option>
              ))}
            </select>
          </Question>
        </FormSection>

        {/* 5. Horoscope Details */}
        <FormSection title="5. Horoscope Charts Upload">
          <Question label="Upload Horoscope Charts">
            <p style={{ fontSize: '12px', color: '#5f6368', marginBottom: '12px' }}>Upload clear images of your Rasi and Amsam horoscope charts</p>
            <div className="photo-upload-section">
              <div className="photo-upload-box" style={{ cursor: 'pointer', transition: 'all 0.2s' }}>
                <div style={{ fontSize: '32px', marginBottom: '8px' }}>♈</div>
                <p style={{ fontWeight: '500', marginBottom: '4px' }}>Horoscope Chart</p>
                <p style={{ fontSize: '12px', color: '#5f6368', marginBottom: '12px' }}>Upload your horoscope chart</p>
                <input type="file" name="rasiChart" accept="image/*" onChange={handleRasiUpload} style={{ fontSize: '13px', color: '#7c3aed', cursor: 'pointer' }} />
              </div>
              {rasiChart && (
                <div style={{ marginTop: '12px' }}>
                  <img src={rasiChart.src} alt="Horoscope Chart" style={{ maxWidth: '100%', borderRadius: '6px', border: '1px solid #e8d5fa' }} />
                  <button type="button" onClick={removeRasiChart} style={{ background: '#d93025', color: 'white', border: 'none', padding: '6px 14px', borderRadius: '4px', marginTop: '8px', cursor: 'pointer', fontSize: '12px' }}>Remove</button>
                </div>
              )}
            </div>
          </Question>
        </FormSection>

        {/* 6. Communication Details */}
        <FormSection title="6. Communication Details">
          <Question label="Permanent Address">
            <textarea name="permanentAddress" placeholder="Your answer" onChange={handleInputChange} style={{ minHeight: '60px' }}></textarea>
          </Question>
          <Question label="Present Address">
            <textarea name="presentAddress" placeholder="Your answer" onChange={handleInputChange} style={{ minHeight: '60px' }}></textarea>
          </Question>
          <Question label="Contact Person Name">
            <input type="text" name="contactPerson" placeholder="Your answer" onChange={handleInputChange} />
          </Question>
          <div className="two-col">
            <Question label="Primary Contact Number" required>
              <input type="tel" name="contactNumber" placeholder="+91 XXXXX XXXXX" maxLength="10" onChange={handleInputChange} required />
            </Question>
            <Question label="Email Address">
              <input type="email" name="email" placeholder="example@email.com" onChange={handleInputChange} />
            </Question>
          </div>
        </FormSection>

        {/* 7. Partner Expectations */}
        <FormSection title="7. Partner Expectations">
          <div className="two-col">
            <Question label="Expected Qualification">
              <input type="text" name="partnerQualification" placeholder="Your answer" onChange={handleInputChange} />
            </Question>
            <Question label="Expected Profession">
              <input type="text" name="partnerJob" placeholder="Your answer" onChange={handleInputChange} />
            </Question>
          </div>

          <Question label="Job Preference">
            <div className="radio-group">
              <label><input type="radio" name="partnerJobRequirement" value="Must Required" onChange={handleInputChange} /> Must</label>
              <label><input type="radio" name="partnerJobRequirement" value="Optional" onChange={handleInputChange} /> Optional</label>
              <label><input type="radio" name="partnerJobRequirement" value="Not Required" onChange={handleInputChange} /> Not Required</label>
            </div>
          </Question>

          <div className="two-col">
            <Question label="Expected Monthly Income (₹)">
              <input type="text" name="partnerIncomeMonth" placeholder="e.g. 20000" onChange={handleInputChange} />
            </Question>
            <Question label="Preferred Age Range">
              <div className="time-group">
                <select name="partnerAgeFrom" onChange={handleInputChange} style={{ width: 'auto' }}>
                  <option value="">From</option>
                  {Array.from({ length: 33 }, (_, i) => i + 18).concat([35, 40, 45, 50]).map(age => (
                    <option key={age} value={age}>{age}</option>
                  ))}
                </select>
                <span style={{ fontSize: '13px', color: '#5f6368' }}>to</span>
                <select name="partnerAgeTo" onChange={handleInputChange} style={{ width: 'auto' }}>
                  <option value="">To</option>
                  {[20, 22, 24, 25, 26, 27, 28, 29, 30, 32, 35, 40, 45, 50].map(age => (
                    <option key={age} value={age}>{age}</option>
                  ))}
                </select>
              </div>
            </Question>
          </div>

          <div className="two-col">
            <Question label="Preferred Diet">
              <div className="radio-group">
                <label><input type="radio" name="partnerDiet" value="Vegetarian" onChange={handleInputChange} /> Vegetarian</label>
                <label><input type="radio" name="partnerDiet" value="Non-Vegetarian" onChange={handleInputChange} /> Non-Vegetarian</label>
                <label><input type="radio" name="partnerDiet" value="Eggetarian" onChange={handleInputChange} /> Eggetarian</label>
              </div>
            </Question>
            <Question label="Horoscope Required?">
              <div className="radio-group">
                <label><input type="radio" name="partnerHoroscopeRequired" value="Yes" onChange={handleInputChange} /> Yes</label>
                <label><input type="radio" name="partnerHoroscopeRequired" value="No" onChange={handleInputChange} /> No</label>
              </div>
            </Question>
          </div>

          <Question label="Preferred Marital Status">
            <div className="check-group">
              <label><input type="checkbox" name="partnerMaritalStatus" value="Unmarried" onChange={handleInputChange} /> Unmarried</label>
              <label><input type="checkbox" name="partnerMaritalStatus" value="Divorced" onChange={handleInputChange} /> Divorced</label>
              <label><input type="checkbox" name="partnerMaritalStatus" value="Widowed" onChange={handleInputChange} /> Widowed</label>
              <label><input type="checkbox" name="partnerMaritalStatus" value="Any" onChange={handleInputChange} /> Doesn't Matter</label>
            </div>
          </Question>

          <div className="two-col">
            <Question label="Preferred Caste">
              <select name="partnerCaste" onChange={handleInputChange}>
                <option value="">Select</option>
                <option value="Any">Any</option>
                <option value="Same">Same as mine</option>
                <option value="Others">Others</option>
              </select>
            </Question>
            <Question label="Preferred Sub Caste">
              <select name="partnerSubCaste" onChange={handleInputChange}>
                <option value="">Select</option>
                <option value="Any">Any</option>
                <option value="Same">Same as mine</option>
                <option value="Others">Others</option>
              </select>
            </Question>
          </div>

          <Question label="Any Other Comments / Expectations">
            <textarea name="partnerOtherRequirement" placeholder="Your answer" onChange={handleInputChange}></textarea>
          </Question>
        </FormSection>

        {/* Terms & Submit */}
        <div className="form-actions vertical">
          <label className="terms-label">
            <input type="checkbox" name="termsAccepted" onChange={handleInputChange} required />
            <span>I accept the <strong>Terms & Conditions</strong>. I confirm that all information provided is true.</span>
          </label>
          <div style={{ display: 'flex', gap: '16px', alignItems: 'center' }}>
            <button className="btn-submit" type="submit">Submit</button>
            <button className="btn-clear" type="reset">Clear form</button>
          </div>
        </div>
      </form>
    </div>
  );
}
