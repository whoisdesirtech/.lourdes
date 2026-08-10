import { onDocumentCreated } from 'firebase-functions/v2/firestore'
import * as logger from 'firebase-functions/logger'
import { defineString } from 'firebase-functions/params'
import * as nodemailer from 'nodemailer'

const organizerEmail = defineString('ORGANIZER_EMAIL')
const smtpHost = defineString('SMTP_HOST')
const smtpPort = defineString('SMTP_PORT')
const smtpUser = defineString('SMTP_USER')
const smtpPass = defineString('SMTP_PASS')

function validate(data: Record<string, unknown>): string | null {
  if (!data.name || typeof data.name !== 'string' || !data.name.trim()) {
    return 'Name is required'
  }
  if (!data.email || typeof data.email !== 'string' || !data.email.trim()) {
    return 'Email is required'
  }
  if (!data.message || typeof data.message !== 'string' || !data.message.trim()) {
    return 'Message is required'
  }
  return null
}

export const onBookingInquiryCreated = onDocumentCreated(
  'bookingInquiries/{docId}',
  async (event) => {
    const snapshot = event.data
    if (!snapshot) {
      logger.warn('No data associated with the event')
      return
    }

    const data = snapshot.data()
    const validationError = validate(data)
    if (validationError) {
      logger.warn(`Validation failed: ${validationError}`)
      return
    }

    const transporter = nodemailer.createTransport({
      host: smtpHost.value(),
      port: parseInt(smtpPort.value(), 10),
      secure: parseInt(smtpPort.value(), 10) === 465,
      auth: {
        user: smtpUser.value(),
        pass: smtpPass.value(),
      },
    })

    const mailOptions: nodemailer.SendMailOptions = {
      from: `"Désir Fils Speaker Page" <${smtpUser.value()}>`,
      to: organizerEmail.value(),
      subject: `New Booking Inquiry from ${data.name}`,
      html: `
        <div style="font-family: sans-serif; max-width: 600px; margin: 0 auto;">
          <h2 style="color: #d4a853;">New Speaking Inquiry</h2>
          <table style="width: 100%; border-collapse: collapse;">
            <tr>
              <td style="padding: 8px 0; color: #888; width: 120px;">Name</td>
              <td style="padding: 8px 0;">${data.name}</td>
            </tr>
            <tr>
              <td style="padding: 8px 0; color: #888;">Email</td>
              <td style="padding: 8px 0;"><a href="mailto:${data.email}">${data.email}</a></td>
            </tr>
            ${data.organization ? `
            <tr>
              <td style="padding: 8px 0; color: #888;">Organization</td>
              <td style="padding: 8px 0;">${data.organization}</td>
            </tr>` : ''}
            ${data.eventName ? `
            <tr>
              <td style="padding: 8px 0; color: #888;">Event Name</td>
              <td style="padding: 8px 0;">${data.eventName}</td>
            </tr>` : ''}
          </table>
          <hr style="border: none; border-top: 1px solid #eee; margin: 16px 0;" />
          <p style="color: #333;">${data.message}</p>
          <hr style="border: none; border-top: 1px solid #eee; margin: 16px 0;" />
          <p style="font-size: 12px; color: #999;">
            Sent from the Désir Fils speaker landing page.
          </p>
        </div>
      `,
    }

    try {
      await transporter.sendMail(mailOptions)
      logger.info('Booking inquiry notification sent', { name: data.name, email: data.email })
    } catch (error) {
      logger.error('Failed to send notification email', error)
    }
  },
)
