import { useState, type FormEvent } from 'react'
import { collection, addDoc, serverTimestamp } from 'firebase/firestore'
import { db } from '../lib/firebase'
import type { SubmitStatus } from '../types/booking'

export default function Contact() {
  const [name, setName] = useState('')
  const [email, setEmail] = useState('')
  const [organization, setOrganization] = useState('')
  const [eventName, setEventName] = useState('')
  const [message, setMessage] = useState('')
  const [status, setStatus] = useState<SubmitStatus>('idle')
  const [error, setError] = useState('')

  const [touched, setTouched] = useState<Record<string, boolean>>({})

  const handleBlur = (field: string) => {
    setTouched(prev => ({ ...prev, [field]: true }))
  }

  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/

  const nameError = touched.name && !name.trim()
  const emailError = touched.email && (!email.trim() || !emailRegex.test(email))
  const messageError = touched.message && !message.trim()

  const isValid = name.trim() && email.trim() && emailRegex.test(email) && message.trim()

  async function handleSubmit(e: FormEvent) {
    e.preventDefault()

    setTouched({ name: true, email: true, message: true })
    if (!isValid) return

    setStatus('submitting')
    setError('')

    try {
      await addDoc(collection(db, 'bookingInquiries'), {
        name: name.trim(),
        email: email.trim(),
        organization: organization.trim() || null,
        eventName: eventName.trim() || null,
        message: message.trim(),
        createdAt: serverTimestamp(),
        status: 'new',
      })

      setStatus('success')
    } catch {
      setError('Something went wrong. Please try again or email us directly.')
      setStatus('error')
    }
  }

  if (status === 'success') {
    return (
      <section id="contact" className="py-20 sm:py-28 bg-dark-900">
        <div className="max-w-xl mx-auto px-4 sm:px-6 text-center">
          <div className="w-16 h-16 rounded-full bg-green-500/10 text-green-400 flex items-center justify-center mx-auto mb-6">
            <svg className="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
            </svg>
          </div>
          <h2 className="text-3xl sm:text-4xl font-display font-bold mb-4">
            Message Sent
          </h2>
          <p className="text-silver-400 font-body mb-8">
            Thanks for reaching out. I'll follow up within 1–2 business days.
          </p>
          <button
            type="button"
            onClick={() => {
              setStatus('idle')
              setName('')
              setEmail('')
              setOrganization('')
              setEventName('')
              setMessage('')
              setTouched({})
            }}
            className="inline-flex items-center px-6 py-3 text-sm font-medium text-dark-900 bg-gold-400 hover:bg-gold-500 rounded-lg transition-all min-h-[44px]"
          >
            Send Another Message
          </button>
        </div>
      </section>
    )
  }

  return (
    <section id="contact" className="py-20 sm:py-28 bg-dark-900">
      <div className="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="text-center mb-12">
          <h2 className="text-3xl sm:text-4xl font-display font-bold mb-4">
            Book Désir for Your Event
          </h2>
          <p className="text-silver-400 font-body max-w-xl mx-auto">
            Interested in having Désir speak at your conference, corporate event, or workshop?
            Fill out the form below and we'll be in touch.
          </p>
          <p className="text-xs text-silver-600 mt-3">
            Or email directly:{' '}
            <a href="mailto:vroland@northmiamifl.gov" className="text-gold-400 hover:text-gold-500 underline">
              vroland@northmiamifl.gov
            </a>
          </p>
        </div>

        <form onSubmit={handleSubmit} className="max-w-xl mx-auto" noValidate>
          <div className="space-y-5">
            <div>
              <label htmlFor="name" className="block text-sm font-medium text-silver-300 mb-1.5">
                Name <span className="text-gold-400">*</span>
              </label>
              <input
                id="name"
                type="text"
                value={name}
                onChange={e => setName(e.target.value)}
                onBlur={() => handleBlur('name')}
                className="w-full px-4 py-3 bg-dark-700 border border-white/10 rounded-lg text-white placeholder-silver-600 focus:outline-none focus:border-gold-400/50 focus:ring-1 focus:ring-gold-400/20 transition-colors min-h-[44px]"
                placeholder="Your name"
              />
              {nameError && <p className="mt-1 text-xs text-red-400">Name is required</p>}
            </div>

            <div>
              <label htmlFor="email" className="block text-sm font-medium text-silver-300 mb-1.5">
                Email <span className="text-gold-400">*</span>
              </label>
              <input
                id="email"
                type="email"
                value={email}
                onChange={e => setEmail(e.target.value)}
                onBlur={() => handleBlur('email')}
                className="w-full px-4 py-3 bg-dark-700 border border-white/10 rounded-lg text-white placeholder-silver-600 focus:outline-none focus:border-gold-400/50 focus:ring-1 focus:ring-gold-400/20 transition-colors min-h-[44px]"
                placeholder="you@example.com"
              />
              {emailError && touched.email && !email.trim() && (
                <p className="mt-1 text-xs text-red-400">Email is required</p>
              )}
              {emailError && touched.email && email.trim() && !emailRegex.test(email) && (
                <p className="mt-1 text-xs text-red-400">Enter a valid email address</p>
              )}
            </div>

            <div className="grid sm:grid-cols-2 gap-4">
              <div>
                <label htmlFor="organization" className="block text-sm font-medium text-silver-300 mb-1.5">
                  Organization
                </label>
                <input
                  id="organization"
                  type="text"
                  value={organization}
                  onChange={e => setOrganization(e.target.value)}
                  className="w-full px-4 py-3 bg-dark-700 border border-white/10 rounded-lg text-white placeholder-silver-600 focus:outline-none focus:border-gold-400/50 focus:ring-1 focus:ring-gold-400/20 transition-colors min-h-[44px]"
                  placeholder="Company or org"
                />
              </div>
              <div>
                <label htmlFor="eventName" className="block text-sm font-medium text-silver-300 mb-1.5">
                  Event Name
                </label>
                <input
                  id="eventName"
                  type="text"
                  value={eventName}
                  onChange={e => setEventName(e.target.value)}
                  className="w-full px-4 py-3 bg-dark-700 border border-white/10 rounded-lg text-white placeholder-silver-600 focus:outline-none focus:border-gold-400/50 focus:ring-1 focus:ring-gold-400/20 transition-colors min-h-[44px]"
                  placeholder="Event name"
                />
              </div>
            </div>

            <div>
              <label htmlFor="message" className="block text-sm font-medium text-silver-300 mb-1.5">
                Message <span className="text-gold-400">*</span>
              </label>
              <textarea
                id="message"
                rows={5}
                value={message}
                onChange={e => setMessage(e.target.value)}
                onBlur={() => handleBlur('message')}
                className="w-full px-4 py-3 bg-dark-700 border border-white/10 rounded-lg text-white placeholder-silver-600 focus:outline-none focus:border-gold-400/50 focus:ring-1 focus:ring-gold-400/20 transition-colors resize-y min-h-[44px]"
                placeholder="Tell me about your event and what you're looking for..."
              />
              {messageError && <p className="mt-1 text-xs text-red-400">Message is required</p>}
            </div>
          </div>

          {error && (
            <div className="mt-4 p-3 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 text-sm">
              {error}
            </div>
          )}

          <div className="mt-8">
            <button
              type="submit"
              disabled={status === 'submitting'}
              className="w-full sm:w-auto px-8 py-4 text-base font-medium text-dark-900 bg-gold-400 hover:bg-gold-500 disabled:opacity-50 disabled:cursor-not-allowed rounded-lg transition-all duration-200 min-h-[52px] shadow-lg shadow-gold-500/20"
            >
              {status === 'submitting' ? 'Sending...' : 'Send Inquiry'}
            </button>
          </div>
        </form>
      </div>
    </section>
  )
}
