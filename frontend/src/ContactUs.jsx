import React, { useState, useEffect } from "react";
import { useTranslation } from "react-i18next";
import { API_BASE, CONTACT_API } from "./config";

export default function Contact() {
  const { t } = useTranslation();
  const [form, setForm] = useState({ name:'', email:'', phone:'', message:'' });
  const [sent, setSent] = useState(false);
  const [sending, setSending] = useState(false);
  const [error, setError] = useState('');
  const [user, setUser] = useState(null); // { mobile, name, cp_id }

  // Check if user is logged in
  useEffect(() => {
    fetch(API_BASE, { method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ action:'contact_check' }), credentials:'include' })
      .then(r => r.json()).then(d => {
        if (d.ok && d.verified && d.mobile) {
          setUser({ mobile: d.mobile, name: d.name || '', cp_id: d.cp_id || '' });
          setForm(f => ({ ...f, phone: d.mobile, name: d.name || f.name }));
        }
      }).catch(() => {});
  }, []);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setSending(true);
    try {
      const resp = await fetch(CONTACT_API, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify({
          action: 'submit',
          name: form.name,
          phone: form.phone,
          email: form.email,
          message: form.message,
          cp_id: user?.cp_id || '',
          mobile: user?.mobile || '',
          is_logged_in: !!user
        })
      });
      const data = await resp.json();
      if (!data.ok) throw new Error(data.error || 'Failed to send');
      setSent(true);
      setForm(f => ({ ...f, message:'', email:'' }));
      setTimeout(() => setSent(false), 5000);
    } catch (err) {
      setError(err.message);
    } finally {
      setSending(false);
    }
  };

  return (
    <div style={{ background:'#f5f5f5', minHeight:'100vh', paddingBottom:70 }}>

      {/* Header */}
      <div style={{ background:'#fff', padding:'20px 16px', borderBottom:'1px solid #f0f0f0', textAlign:'center' }}>
        <h1 style={{ fontSize:22, fontWeight:700, color:'#222', margin:0 }}>Contact Us</h1>
        <p style={{ fontSize:14.3, color:'#999', marginTop:4 }}>We'd love to hear from you</p>
      </div>

      <div style={{ maxWidth:600, margin:'0 auto', padding:'12px' }}>

        {/* Contact Cards */}
        <div style={{ display:'flex', flexDirection:'column', gap:10, marginBottom:12 }}>

          {/* Phone */}
          <a href="tel:+919025316833" style={{ display:'flex', alignItems:'center', gap:14, background:'#fff', borderRadius:12, padding:'16px 18px', border:'1px solid #f0f0f0', boxShadow:'0 1px 4px rgba(0,0,0,0.04)', textDecoration:'none' }}>
            <div style={{ width:44, height:44, borderRadius:10, background:'#f0fdf4', display:'flex', alignItems:'center', justifyContent:'center', flexShrink:0 }}>
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#16a34a" strokeWidth="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            </div>
            <div>
              <div style={{ fontSize:12.1, fontWeight:700, color:'#999', textTransform:'uppercase', letterSpacing:0.5 }}>Phone</div>
              <div style={{ fontSize:17.6, fontWeight:700, color:'#16a34a' }}>+91 90253 16833</div>
            </div>
          </a>

          {/* WhatsApp */}
          <a href="https://wa.me/919025316833" target="_blank" rel="noopener noreferrer" style={{ display:'flex', alignItems:'center', gap:14, background:'#fff', borderRadius:12, padding:'16px 18px', border:'1px solid #f0f0f0', boxShadow:'0 1px 4px rgba(0,0,0,0.04)', textDecoration:'none' }}>
            <div style={{ width:44, height:44, borderRadius:10, background:'#f0fdf4', display:'flex', alignItems:'center', justifyContent:'center', flexShrink:0 }}>
              <svg width="20" height="20" viewBox="0 0 24 24" fill="#25D366"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
            </div>
            <div>
              <div style={{ fontSize:12.1, fontWeight:700, color:'#999', textTransform:'uppercase', letterSpacing:0.5 }}>WhatsApp</div>
              <div style={{ fontSize:17.6, fontWeight:700, color:'#25D366' }}>+91 90253 16833</div>
            </div>
          </a>

          {/* Email */}
          <a href="mailto:info@chennaiprofile.in" style={{ display:'flex', alignItems:'center', gap:14, background:'#fff', borderRadius:12, padding:'16px 18px', border:'1px solid #f0f0f0', boxShadow:'0 1px 4px rgba(0,0,0,0.04)', textDecoration:'none' }}>
            <div style={{ width:44, height:44, borderRadius:10, background:'#eff6ff', display:'flex', alignItems:'center', justifyContent:'center', flexShrink:0 }}>
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2563eb" strokeWidth="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            </div>
            <div>
              <div style={{ fontSize:12.1, fontWeight:700, color:'#999', textTransform:'uppercase', letterSpacing:0.5 }}>Email</div>
              <div style={{ fontSize:15.4, fontWeight:600, color:'#2563eb' }}>info@chennaiprofile.in</div>
            </div>
          </a>

          {/* Address */}
          <div style={{ display:'flex', alignItems:'flex-start', gap:14, background:'#fff', borderRadius:12, padding:'16px 18px', border:'1px solid #f0f0f0', boxShadow:'0 1px 4px rgba(0,0,0,0.04)' }}>
            <div style={{ width:44, height:44, borderRadius:10, background:'#fef2f2', display:'flex', alignItems:'center', justifyContent:'center', flexShrink:0 }}>
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#8B0000" strokeWidth="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            </div>
            <div>
              <div style={{ fontSize:12.1, fontWeight:700, color:'#999', textTransform:'uppercase', letterSpacing:0.5 }}>Office Address</div>
              <div style={{ fontSize:15.4, fontWeight:600, color:'#333', lineHeight:1.6 }}>
                No. 45, Big Street,<br/>
                Near Mahamaham Tank,<br/>
                Chennai - 600001,<br/>
                Thanjavur District, Tamil Nadu
              </div>
            </div>
          </div>

          {/* Working Hours */}
          <div style={{ display:'flex', alignItems:'center', gap:14, background:'#fff', borderRadius:12, padding:'16px 18px', border:'1px solid #f0f0f0', boxShadow:'0 1px 4px rgba(0,0,0,0.04)' }}>
            <div style={{ width:44, height:44, borderRadius:10, background:'#fff7ed', display:'flex', alignItems:'center', justifyContent:'center', flexShrink:0 }}>
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ea580c" strokeWidth="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div>
              <div style={{ fontSize:12.1, fontWeight:700, color:'#999', textTransform:'uppercase', letterSpacing:0.5 }}>Working Hours</div>
              <div style={{ fontSize:15.4, fontWeight:600, color:'#333' }}>Mon - Sat: 9:00 AM - 7:00 PM</div>
              <div style={{ fontSize:13.2, color:'#999' }}>Sunday: Closed</div>
            </div>
          </div>
        </div>

        {/* Contact Form */}
        <div style={{ background:'#fff', borderRadius:12, padding:'20px 18px', border:'1px solid #f0f0f0', boxShadow:'0 1px 4px rgba(0,0,0,0.04)' }}>
          <div style={{ fontSize:16.5, fontWeight:700, color:'#222', marginBottom:16 }}>Send us a Message</div>

          {user && (
            <div style={{ background:'#eff6ff', border:'1px solid #bfdbfe', borderRadius:8, padding:'10px 14px', marginBottom:14, fontSize:14.3, color:'#2563eb', fontWeight:500, display:'flex', alignItems:'center', gap:8 }}>
              <span>Logged in as <strong>{user.mobile}</strong>{user.name ? ` (${user.name})` : ''}</span>
            </div>
          )}

          {sent && (
            <div style={{ background:'#f0fdf4', border:'1px solid #bbf7d0', borderRadius:8, padding:'10px 14px', marginBottom:14, fontSize:14.3, color:'#16a34a', fontWeight:600 }}>
              Message sent successfully! We'll get back to you soon.
            </div>
          )}

          {error && (
            <div style={{ background:'#fef2f2', border:'1px solid #fecaca', borderRadius:8, padding:'10px 14px', marginBottom:14, fontSize:14.3, color:'#dc2626', fontWeight:600 }}>
              {error}
            </div>
          )}

          <form onSubmit={handleSubmit}>
            <div style={{ marginBottom:12 }}>
              <label style={{ fontSize:12.1, fontWeight:700, color:'#888', textTransform:'uppercase', letterSpacing:0.5, display:'block', marginBottom:4 }}>Your Name</label>
              <input value={form.name} onChange={e => setForm({...form, name:e.target.value})} required
                placeholder="Enter your name"
                style={{ width:'100%', padding:'10px 12px', border:'1.5px solid #e0e0e0', borderRadius:8, fontSize:15.4, outline:'none', boxSizing:'border-box' }} />
            </div>
            <div style={{ marginBottom:12 }}>
              <label style={{ fontSize:12.1, fontWeight:700, color:'#888', textTransform:'uppercase', letterSpacing:0.5, display:'block', marginBottom:4 }}>Phone Number</label>
              <input value={form.phone} onChange={e => setForm({...form, phone:e.target.value})} required
                placeholder="Enter your phone number" type="tel"
                readOnly={!!user}
                style={{ width:'100%', padding:'10px 12px', border:'1.5px solid #e0e0e0', borderRadius:8, fontSize:15.4, outline:'none', boxSizing:'border-box', background: user ? '#f9fafb' : '#fff' }} />
            </div>
            <div style={{ marginBottom:12 }}>
              <label style={{ fontSize:12.1, fontWeight:700, color:'#888', textTransform:'uppercase', letterSpacing:0.5, display:'block', marginBottom:4 }}>Email (Optional)</label>
              <input value={form.email} onChange={e => setForm({...form, email:e.target.value})}
                placeholder="Enter your email" type="email"
                style={{ width:'100%', padding:'10px 12px', border:'1.5px solid #e0e0e0', borderRadius:8, fontSize:15.4, outline:'none', boxSizing:'border-box' }} />
            </div>
            <div style={{ marginBottom:14 }}>
              <label style={{ fontSize:12.1, fontWeight:700, color:'#888', textTransform:'uppercase', letterSpacing:0.5, display:'block', marginBottom:4 }}>Message</label>
              <textarea value={form.message} onChange={e => setForm({...form, message:e.target.value})} required
                placeholder="Type your message here..." rows={4}
                style={{ width:'100%', padding:'10px 12px', border:'1.5px solid #e0e0e0', borderRadius:8, fontSize:15.4, outline:'none', boxSizing:'border-box', resize:'vertical' }} />
            </div>
            <button type="submit" disabled={sending}
              style={{ width:'100%', padding:'12px', background: sending ? '#999' : 'linear-gradient(135deg,#8B0000,#C41E3A)', color:'#fff', border:'none', borderRadius:8, fontSize:15.4, fontWeight:700, cursor: sending ? 'not-allowed' : 'pointer' }}>
              {sending ? 'Sending...' : 'Send Message'}
            </button>
          </form>
        </div>

      </div>
    </div>
  );
}
