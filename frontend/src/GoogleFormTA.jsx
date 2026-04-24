import React, { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { useNavigate } from 'react-router-dom';
import './GoogleFormStyle.css';

export default function GoogleFormTA() {
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
      alert('அதிகபட்சம் 2 புகைப்படங்களுக்கு அனுமதி உண்டு');
      event.target.value = '';
      return;
    }

    files.forEach(file => {
      if (file.size > 30 * 1024 * 1024) {
        alert('கோப்பு அளவு 30MB க்கும் குறைவாக இருக்க வேண்டும்');
        event.target.value = '';
        return;
      }
      if (!file.type.startsWith('image/')) {
        alert('செல்லுபடியான படக் கோப்பைத் தேர்ந்தெடுக்கவும்');
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
        alert('கோப்பு அளவு 5MB க்கும் குறைவாக இருக்க வேண்டும்');
        event.target.value = '';
        return;
      }
      if (!file.type.startsWith('image/')) {
        alert('செல்லுபடியான படக் கோப்பைத் தேர்ந்தெடுக்கவும்');
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
    
    Object.entries(formData).forEach(([key, value]) => {
      formDataToSend.append(key, value);
    });
    
    selectedPhotos.forEach((photo, index) => {
      formDataToSend.append(`photo_${index}`, photo.file);
    });
    
    if (rasiChart) {
      formDataToSend.append('rasiChart', rasiChart.file);
    }

    try {
      const response = await fetch('/API/register.php', {
        method: 'POST',
        body: formDataToSend
      });
      
      if (response.ok) {
        alert('சுயவிவரம் வெற்றிகரமாக பதிவு செய்யப்பட்டது!');
        e.target.reset();
        setSelectedPhotos([]);
        setRasiChart(null);
        setFormData({});
      } else {
        alert('சுயவிவரத்தைப் பதிவு செய்ய முடியவில்லை. மீண்டும் முயற்சி செய்யவும்.');
      }
    } catch (error) {
      console.error('Error:', error);
      alert('படிவத்தைச் சமர்ப்பிக்கும்போது பிழை. மீண்டும் முயற்சி செய்யவும்.');
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
    <div className="form-container" lang="ta">
      {/* Header */}
      <div className="form-header" style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', gap: '24px' }}>
        <div style={{ flex: 1 }}>
          <h1>சென்னை சுயவிவரங்கள்</h1>
          <div className="subtitle">உங்கள் சுயவிவரத்தைப் பதிவு செய்யவும்</div>
          <div className="notice">
            <strong>திருமண சுயவிவர பதிவு படிவம்</strong><br />
            <strong style={{ color: '#d93025' }}>*</strong> குறிப்பிடப்பட்ட அனைத்து கட்டாய புலங்களை நிரப்பவும்<br />
            உங்கள் தகவல் கன்ஃபிடென்ஷியல் மற்றும் பாதுகாப்பாக இருக்கும்.
          </div>
          <div className="required-note">* கட்டாய புலங்கள்</div>
        </div>
        <div style={{ textAlign: 'right', paddingTop: '8px' }}>
          <div style={{ fontSize: '14px', fontWeight: '500', marginBottom: '12px', color: '#202124' }}>
            To change the language
          </div>
          <div style={{ display: 'flex', flexDirection: 'column', gap: '8px', minWidth: '120px' }}>
            <button 
              onClick={() => navigate('/google-form')}
              style={{ padding: '8px 16px', border: '1px solid #dadce0', backgroundColor: '#fff', borderRadius: '4px', cursor: 'pointer', fontSize: '13px', fontWeight: '500', color: '#1f2937', transition: 'all 0.2s' }}
              onMouseEnter={(e) => e.target.style.backgroundColor = '#f8f9fa'}
              onMouseLeave={(e) => e.target.style.backgroundColor = '#fff'}
            >
              English
            </button>
          </div>
        </div>
      </div>

      <form id="matrimonyForm" onSubmit={handleSubmit}>
        {/* 1. Personal & Family Details */}
        <FormSection title="1. தனிப்பட்ட மற்றும் குடும்பத் தகவல்">
          <Question label="பெயர்" required>
            <input type="text" name="name" placeholder="உங்கள் பெயர்" onChange={handleInputChange} required />
          </Question>

          <Question label="பாலினம்" required>
            <div className="radio-group">
              <label><input type="radio" name="gender" value="Male" onChange={handleInputChange} required /> ஆண்</label>
              <label><input type="radio" name="gender" value="Female" onChange={handleInputChange} /> பெண்</label>
            </div>
          </Question>

          <div className="two-col">
            <Question label="பிறந்த தேதி" required>
              <input type="date" name="dob" onChange={handleInputChange} required />
            </Question>
            <Question label="பிறந்த நேரம்">
              <div className="time-group">
                <select name="birthHour" onChange={handleInputChange} style={{ width: 'auto' }}>
                  <option value="">மணி</option>
                  {Array.from({ length: 24 }, (_, i) => (
                    <option key={i} value={String(i).padStart(2, '0')}>
                      {String(i).padStart(2, '0')}
                    </option>
                  ))}
                </select>
                <select name="birthMin" onChange={handleInputChange} style={{ width: 'auto' }}>
                  <option value="">நிமிடம்</option>
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
            <Question label="பிறந்த இடம்" required>
              <input type="text" name="placeBirth" placeholder="நகரம்/ஊர் பெயர்" onChange={handleInputChange} required />
            </Question>
            <Question label="நாட்டுரிமை" required>
              <input type="text" name="nativity" placeholder="உங்கள் நிகர் மாநிலம்" onChange={handleInputChange} required />
            </Question>
          </div>

          <div className="two-col">
            <Question label="தாய் மொழி" required>
              <select name="motherTongue" onChange={handleInputChange} required>
                <option value="">தேர்ந்தெடுக்கவும்</option>
                <option value="Tamil">தமிழ்</option>
                <option value="Telugu">తెలుగు</option>
                <option value="Kannada">ಕನ್ನಡ</option>
                <option value="Malayalam">മലയാളം</option>
                <option value="Hindi">హిందీ</option>
                <option value="Marathi">मराठी</option>
                <option value="English">ஆங்கிலம்</option>
              </select>
            </Question>
            <Question label="திருமண நிலை" required>
              <div className="radio-group">
                <label><input type="radio" name="maritalStatus" value="Unmarried" onChange={handleInputChange} required /> திருமணமாகாதவர்</label>
                <label><input type="radio" name="maritalStatus" value="Married" onChange={handleInputChange} /> திருமணமான</label>
                <label><input type="radio" name="maritalStatus" value="Divorced" onChange={handleInputChange} /> விவாகரத்து</label>
                <label><input type="radio" name="maritalStatus" value="Widowed" onChange={handleInputChange} /> விதவை</label>
              </div>
            </Question>
          </div>

          <div className="two-col">
            <Question label="தந்தையின் பெயர்">
              <input type="text" name="fatherName" placeholder="தந்தையின் முழு பெயர்" onChange={handleInputChange} />
            </Question>
            <Question label="தந்தை">
              <div className="radio-group">
                <label><input type="radio" name="fatherAlive" value="yes" onChange={handleInputChange} /> உயிருடன்</label>
                <label><input type="radio" name="fatherAlive" value="no" onChange={handleInputChange} /> இறந்தவர்</label>
              </div>
            </Question>
          </div>

          <Question label="தந்தையின் தொழில்">
            <input type="text" name="fatherJob" placeholder="தந்தையின் தொழில்" onChange={handleInputChange} />
          </Question>

          <div className="two-col">
            <Question label="தாயின் பெயர்">
              <input type="text" name="motherName" placeholder="தாயின் முழு பெயர்" onChange={handleInputChange} />
            </Question>
            <Question label="தாய்">
              <div className="radio-group">
                <label><input type="radio" name="motherAlive" value="yes" onChange={handleInputChange} /> உயிருடன்</label>
                <label><input type="radio" name="motherAlive" value="no" onChange={handleInputChange} /> இறந்தவர்</label>
              </div>
            </Question>
          </div>

          <Question label="தாயின் தொழில்">
            <input type="text" name="motherJob" placeholder="தாயின் தொழில்" onChange={handleInputChange} />
          </Question>

          <Question label="சகோதரர்/சோதரிமார்">
            <table className="sibling-table">
              <thead>
                <tr>
                  <th>வகை</th>
                  <th>மூத்த சகோதரன்</th>
                  <th>இளைய சகோதரன்</th>
                  <th>மூத்த சோதரி</th>
                  <th>இளைய சோதரி</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><strong>திருமணமான</strong></td>
                  <td><select name="sibMarriedEB" onChange={handleInputChange}><option>-</option><option>0</option><option>1</option><option>2</option></select></td>
                  <td><select name="sibMarriedYB" onChange={handleInputChange}><option>-</option><option>0</option><option>1</option><option>2</option></select></td>
                  <td><select name="sibMarriedES" onChange={handleInputChange}><option>-</option><option>0</option><option>1</option><option>2</option></select></td>
                  <td><select name="sibMarriedYS" onChange={handleInputChange}><option>-</option><option>0</option><option>1</option><option>2</option></select></td>
                </tr>
                <tr>
                  <td><strong>திருமணமாகாதவர்</strong></td>
                  <td><select name="sibUnmarriedEB" onChange={handleInputChange}><option>-</option><option>0</option><option>1</option><option>2</option></select></td>
                  <td><select name="sibUnmarriedYB" onChange={handleInputChange}><option>-</option><option>0</option><option>1</option><option>2</option></select></td>
                  <td><select name="sibUnmarriedES" onChange={handleInputChange}><option>-</option><option>0</option><option>1</option><option>2</option></select></td>
                  <td><select name="sibUnmarriedYS" onChange={handleInputChange}><option>-</option><option>0</option><option>1</option><option>2</option></select></td>
                </tr>
              </tbody>
            </table>
          </Question>

          <Question label="கூடுதல் விவரங்கள்">
            <textarea name="others" placeholder="திறமைகள், சாதனைகள், வீசா நிலை, குடும்ப தெய்வம், முதலியவை" onChange={handleInputChange}></textarea>
          </Question>

          <Question label="உங்கள் புகைப்படங்களை பதிவேற்றவும்">
            <p style={{ fontSize: '12px', color: '#5f6368', marginBottom: '12px' }}>2 வரை புகைப்படங்களை பதிவேற்றவும்</p>
            <div className="photo-upload-box" style={{ cursor: 'pointer', transition: 'all 0.2s' }}>
              <div style={{ fontSize: '32px', marginBottom: '8px' }}>📷</div>
              <p style={{ fontWeight: '500', marginBottom: '4px' }}>உங்கள் புகைப்படங்களைத் தேர்ந்தெடுக்கவும்</p>
              <input type="file" name="photos" accept="image/*" multiple onChange={handleMultiplePhotoUpload} style={{ fontSize: '13px', color: '#7c3aed', cursor: 'pointer' }} />
            </div>
            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(150px, 1fr))', gap: '12px', marginTop: '16px' }}>
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
        <FormSection title="2. உடல் பண்புகள்">
          <div className="two-col">
            <Question label="உயரம்">
              <select name="height" onChange={handleInputChange}>
                <option value="">தேர்ந்தெடுக்கவும்</option>
                {['4\'8"', '4\'9"', '4\'10"', '4\'11"', '5\'0"', '5\'1"', '5\'2"', '5\'3"', '5\'4"', '5\'5"', '5\'6"', '5\'7"', '5\'8"', '5\'9"', '5\'10"', '5\'11"', '6\'0"'].map(h => (
                  <option key={h} value={h}>{h}</option>
                ))}
              </select>
            </Question>
            <Question label="எடை">
              <select name="weight" onChange={handleInputChange}>
                <option value="">தேர்ந்தெடுக்கவும் (கிலோ)</option>
                {[40, 45, 50, 55, 60, 65, 70, 75].map(w => (
                  <option key={w} value={`${w}kg`}>{w}கி.கி</option>
                ))}
              </select>
            </Question>
          </div>

          <div className="two-col">
            <Question label="இரத்த வகை">
              <select name="bloodGroup" onChange={handleInputChange}>
                <option value="">தேர்ந்தெடுக்கவும்</option>
                {['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-'].map(bg => (
                  <option key={bg} value={bg}>{bg}</option>
                ))}
              </select>
            </Question>
            <Question label="சரும நிறம்">
              <select name="complexion" onChange={handleInputChange}>
                <option value="">தேர்ந்தெடுக்கவும்</option>
                <option value="Very Fair">மிக வெளுப்பாக</option>
                <option value="Fair">வெளுப்பாக</option>
                <option value="Wheatish">கோதுமை நிறம்</option>
                <option value="Dark">இருண்ட நிறம்</option>
              </select>
            </Question>
          </div>

          <div className="two-col">
            <Question label="உணவு">
              <div className="radio-group">
                <label><input type="radio" name="diet" value="Vegetarian" onChange={handleInputChange} /> சைவ உணவு</label>
                <label><input type="radio" name="diet" value="Non-Vegetarian" onChange={handleInputChange} /> அசைவ உணவு</label>
              </div>
            </Question>
            <Question label="ஏதேனும் குறைபாடு">
              <div className="radio-group">
                <label><input type="radio" name="disability" value="yes" onChange={handleInputChange} /> ஆம்</label>
                <label><input type="radio" name="disability" value="no" onChange={handleInputChange} /> இல்லை</label>
              </div>
            </Question>
          </div>
        </FormSection>

        {/* 3. Education & Occupation */}
        <FormSection title="3. கல்வி மற்றும் தொழில் விவரங்கள்">
          <div className="two-col">
            <Question label="கல்வி தகுதி">
              <input type="text" name="qualification" placeholder="எ.கா. B.E., M.Sc., MBA" onChange={handleInputChange} />
            </Question>
            <Question label="வேலை/தொழில்">
              <input type="text" name="job" placeholder="உங்கள் பதிலை உள்ளிடவும்" onChange={handleInputChange} />
            </Question>
          </div>

          <div className="two-col">
            <Question label="பணிபுரி இடம்">
              <input type="text" name="placeJob" placeholder="உங்கள் பதிலை உள்ளிடவும்" onChange={handleInputChange} />
            </Question>
            <Question label="மாதாந்தர வருமானம் (₹)">
              <input type="text" name="incomeMonth" placeholder="எ.கா. 25000" onChange={handleInputChange} />
            </Question>
          </div>
        </FormSection>

        {/* 4. Astrology Details */}
        <FormSection title="4. ஜோதிட விவரங்கள்">
          <div className="two-col">
            <Question label="சாதி">
              <select name="caste" onChange={handleInputChange}>
                <option value="">தேர்ந்தெடுக்கவும்</option>
                {['பிராமணர்', 'முதலியார்', 'நாடார்', 'கபு', 'பட்டியல் சாதி', 'பின்தங்கிய வகுப்பு'].map(c => (
                  <option key={c} value={c}>{c}</option>
                ))}
              </select>
            </Question>
            <Question label="உப-சாதி">
              <input type="text" name="subCaste" placeholder="உப-சாதி பெயர்" onChange={handleInputChange} />
            </Question>
          </div>

          <Question label="கோத்திரம்">
            <input type="text" name="gothram" placeholder="உங்கள் பதிลை உள்ளிடவும்" onChange={handleInputChange} />
          </Question>

          <div className="three-col">
            <Question label="நட்சத்திரம்">
              <select name="star" onChange={handleInputChange}>
                <option value="">தேர்ந்தெடுக்கவும்</option>
                {['அசுவினி', 'பரணி', 'கார்த்திகை', 'ரோகிணி', 'திருவாதிரை', 'புனர்பூசம்', 'பூசம்', 'ஆயில்யம்', 'மகம்', 'பூரம்', 'உத்திரம்', 'ஹஸ்தம்'].map(s => (
                  <option key={s} value={s}>{s}</option>
                ))}
              </select>
            </Question>
            <Question label="ராசி">
              <select name="raasi" onChange={handleInputChange}>
                <option value="">தேர்ந்தெடுக்கவும்</option>
                {['மேஷம்', 'ரிஷபம்', 'மிதுனம்', 'கடகம்', 'சிம்மம்', 'கன்னி', 'துலாம்', 'விருச்சிகம்', 'தனுசு', 'மகரம்', 'கும்பம்', 'மீனம்'].map(r => (
                  <option key={r} value={r}>{r}</option>
                ))}
              </select>
            </Question>
            <Question label="பாதம்">
              <select name="padam" onChange={handleInputChange}>
                <option value="">தேர்ந்தெடுக்கவும்</option>
                {[1, 2, 3, 4].map(p => (
                  <option key={p} value={p}>{p}</option>
                ))}
              </select>
            </Question>
          </div>

          <Question label="லக்னம்">
            <select name="laknam" onChange={handleInputChange}>
              <option value="">லக்னம் தேர்ந்தெடுக்கவும்</option>
              {['மேஷம்', 'ரிஷபம்', 'மிதுனம்', 'கடகம்', 'சிம்மம்', 'கன்னி', 'துலாம்', 'விருச்சிகம்', 'தனுசு', 'மகரம்', 'கும்பம்', 'மீனம்'].map(l => (
                <option key={l} value={l}>{l}</option>
              ))}
            </select>
          </Question>
        </FormSection>

        {/* 5. Horoscope Details */}
        <FormSection title="5. ஜோதிட சரிணைகளைப் பதிவேற்றவும்">
          <Question label="ஜோதிட சரிணைகளைப் பதிவேற்றவும்">
            <p style={{ fontSize: '12px', color: '#5f6368', marginBottom: '12px' }}>உங்கள் ராசி சரிணை மற்றும் அம்சம் சரிணையின் தெளிவான படங்களைப் பதிவேற்றவும்</p>
            <div className="photo-upload-section">
              <div className="photo-upload-box" style={{ cursor: 'pointer', transition: 'all 0.2s' }}>
                <div style={{ fontSize: '32px', marginBottom: '8px' }}>♈</div>
                <p style={{ fontWeight: '500', marginBottom: '4px' }}>ஜோதிட சரிணை</p>
                <p style={{ fontSize: '12px', color: '#5f6368', marginBottom: '12px' }}>உங்கள் ஜோதிட சரிணையைப் பதிவேற்றவும்</p>
                <input type="file" name="rasiChart" accept="image/*" onChange={handleRasiUpload} style={{ fontSize: '13px', color: '#7c3aed', cursor: 'pointer' }} />
              </div>
              {rasiChart && (
                <div style={{ marginTop: '12px' }}>
                  <img src={rasiChart.src} alt="ஜோதிட சரிணை" style={{ maxWidth: '100%', borderRadius: '6px', border: '1px solid #e8d5fa' }} />
                  <button type="button" onClick={removeRasiChart} style={{ background: '#d93025', color: 'white', border: 'none', padding: '6px 14px', borderRadius: '4px', marginTop: '8px', cursor: 'pointer', fontSize: '12px' }}>அகற்று</button>
                </div>
              )}
            </div>
          </Question>
        </FormSection>

        {/* 6. Communication Details */}
        <FormSection title="6. தொடர்பு விவரங்கள்">
          <Question label="நிரந்தர முகவரி">
            <textarea name="permanentAddress" placeholder="உங்கள் பதிலை உள்ளிடவும்" onChange={handleInputChange} style={{ minHeight: '60px' }}></textarea>
          </Question>
          <Question label="தற்போதைய முகவரி">
            <textarea name="presentAddress" placeholder="உங்கள் பதிலை உள்ளிடவும்" onChange={handleInputChange} style={{ minHeight: '60px' }}></textarea>
          </Question>
          <Question label="தொடர்பு நபர் பெயர்">
            <input type="text" name="contactPerson" placeholder="உங்கள் பதிலை உள்ளிடவும்" onChange={handleInputChange} />
          </Question>
          <div className="two-col">
            <Question label="முதன்மை தொலைபேசி எண்" required>
              <input type="tel" name="contactNumber" placeholder="+91 XXXXX XXXXX" maxLength="10" onChange={handleInputChange} required />
            </Question>
            <Question label="மின்னஞ்சல் முகவரி">
              <input type="email" name="email" placeholder="example@email.com" onChange={handleInputChange} />
            </Question>
          </div>
        </FormSection>

        {/* 7. Partner Expectations */}
        <FormSection title="7. துணை எதிர்பார்ப்புகள்">
          <div className="two-col">
            <Question label="எதிர்பார்க்கப்பட்ட தகுதி">
              <input type="text" name="partnerQualification" placeholder="உங்கள் பதிலை உள்ளிடவும்" onChange={handleInputChange} />
            </Question>
            <Question label="எதிர்பார்க்கப்பட்ட வேலை">
              <input type="text" name="partnerJob" placeholder="உங்கள் பதிலை உள்ளிடவும்" onChange={handleInputChange} />
            </Question>
          </div>

          <Question label="வேலை விருப்பம்">
            <div className="radio-group">
              <label><input type="radio" name="partnerJobRequirement" value="Must Required" onChange={handleInputChange} /> கட்டாயமாக</label>
              <label><input type="radio" name="partnerJobRequirement" value="Optional" onChange={handleInputChange} /> விரும்பதற்குரியது</label>
              <label><input type="radio" name="partnerJobRequirement" value="Not Required" onChange={handleInputChange} /> தேவை இல்லை</label>
            </div>
          </Question>

          <div className="two-col">
            <Question label="எதிர்பார்க்கப்பட்ட மாதாந்தர வருமானம் (₹)">
              <input type="text" name="partnerIncomeMonth" placeholder="எ.கா. 20000" onChange={handleInputChange} />
            </Question>
            <Question label="விருப்பமான வயது வரம்பு">
              <div className="time-group">
                <select name="partnerAgeFrom" onChange={handleInputChange} style={{ width: 'auto' }}>
                  <option value="">இதிலிருந்து</option>
                  {[18, 20, 22, 25, 30, 35, 40].map(age => (
                    <option key={age} value={age}>{age}</option>
                  ))}
                </select>
                <span style={{ fontSize: '13px', color: '#5f6368' }}>வரை</span>
                <select name="partnerAgeTo" onChange={handleInputChange} style={{ width: 'auto' }}>
                  <option value="">வரை</option>
                  {[22, 25, 30, 35, 40, 45, 50].map(age => (
                    <option key={age} value={age}>{age}</option>
                  ))}
                </select>
              </div>
            </Question>
          </div>

          <div className="two-col">
            <Question label="விருப்பமான உணவு">
              <div className="radio-group">
                <label><input type="radio" name="partnerDiet" value="Vegetarian" onChange={handleInputChange} /> சைவ உணவு</label>
                <label><input type="radio" name="partnerDiet" value="Non-Vegetarian" onChange={handleInputChange} /> அசைவ உணவு</label>
              </div>
            </Question>
            <Question label="ஜோதிடம் தேவைக்கு?">
              <div className="radio-group">
                <label><input type="radio" name="partnerHoroscopeRequired" value="Yes" onChange={handleInputChange} /> ஆம்</label>
                <label><input type="radio" name="partnerHoroscopeRequired" value="No" onChange={handleInputChange} /> இல்லை</label>
              </div>
            </Question>
          </div>

          <Question label="விருப்பமான திருமண நிலை">
            <div className="check-group">
              <label><input type="checkbox" name="partnerMaritalStatus" value="Unmarried" onChange={handleInputChange} /> திருமணமாகாதவர்</label>
              <label><input type="checkbox" name="partnerMaritalStatus" value="Divorced" onChange={handleInputChange} /> விவாகரத்து</label>
              <label><input type="checkbox" name="partnerMaritalStatus" value="Widowed" onChange={handleInputChange} /> விதவை</label>
              <label><input type="checkbox" name="partnerMaritalStatus" value="Any" onChange={handleInputChange} /> பரவாலில்லை</label>
            </div>
          </Question>

          <div className="two-col">
            <Question label="விருப்பமான சாதி">
              <select name="partnerCaste" onChange={handleInputChange}>
                <option value="">தேர்ந்தெடுக்கவும்</option>
                <option value="Brahmin">பிராமணர்</option>
                <option value="Mudaliar">முதலியார்</option>
                <option value="Nadar">நாடார்</option>
                <option value="Any">எந்தவொன்றும்</option>
              </select>
            </Question>
            <Question label="விருப்பமான உப-சாதி">
              <select name="partnerSubCaste" onChange={handleInputChange}>
                <option value="">தேர்ந்தெடுக்கவும்</option>
                <option value="Same">ஒரே உப-சாதி</option>
                <option value="Any">எந்தவொன்றும்</option>
              </select>
            </Question>
          </div>

          <Question label="பிற குறிப்புகள் / எதிர்பார்ப்புகள்">
            <textarea name="partnerOtherRequirement" placeholder="உங்கள் பதிலை உள்ளிடவும்" onChange={handleInputChange}></textarea>
          </Question>
        </FormSection>

        {/* Terms & Submit */}
        <div className="form-actions vertical">
          <label className="terms-label">
            <input type="checkbox" name="termsAccepted" onChange={handleInputChange} required />
            <span>நான் <strong>நிபந்தனைகள் மற்றும் விதிமுறைகள்</strong> ஒப்புக்கொள்கிறேன். வழங்கிய அனைத்து தகவல் உண்மை எனக் குறிப்பிடுகிறேன்.</span>
          </label>
          <div style={{ display: 'flex', gap: '16px', alignItems: 'center' }}>
            <button className="btn-submit" type="submit">சமர்ப்பி</button>
            <button className="btn-clear" type="reset">படிவத்தை மீட்டமை</button>
          </div>
        </div>
      </form>
    </div>
  );
}
