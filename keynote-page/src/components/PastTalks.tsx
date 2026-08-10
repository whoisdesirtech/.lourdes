const talks = [
  {
    title: 'Creative Hub AI — Live Demo',
    subtitle: 'AI-Powered Stock Valuation & Market Analysis',
    description:
      'A live walkthrough of Creative Hub AI, an AI-powered tool that analyzes stock data, generates valuations, and provides market insights. Demo includes real-time data processing, model inference, and actionable outputs.',
    tags: ['AI', 'Finance', 'Live Demo'],
    date: '[DATE TBD]',
    type: 'featured',
  },
  {
    title: 'AI in Marketing: What Actually Works',
    subtitle: 'Industry Panel',
    description:
      'Panel discussion on AI applications in digital marketing — covering content generation, audience segmentation, predictive analytics, and ethical considerations for small to mid-size brands.',
    tags: ['Marketing', 'Panel'],
    date: '2025',
    type: 'panel',
  },
  {
    title: 'Practical AI Workflows for Entrepreneurs',
    subtitle: 'Webinar',
    description:
      'Walked entrepreneurs through AI tools for customer service automation, social media content production, bookkeeping, and operations — with live demos and actionable playbooks.',
    tags: ['Webinar', 'AI Tools'],
    date: '2025',
    type: 'webinar',
  },
  {
    title: 'Building Your Brand with Digital Media',
    subtitle: 'Marketing Workshop',
    description:
      'Workshop on digital media strategy, content creation workflows, and using AI-assisted tools to scale content production without sacrificing quality or brand voice.',
    tags: ['Workshop', 'Content'],
    date: '2024',
    type: 'workshop',
  },
]

export default function PastTalks() {
  return (
    <section id="past-talks" className="py-20 sm:py-28 bg-dark-800">
      <div className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="text-center mb-16">
          <h2 className="text-3xl sm:text-4xl font-display font-bold mb-4">
            Past Talks
          </h2>
          <p className="text-silver-400 font-body max-w-2xl mx-auto">
            Featured keynotes, panels, and workshops. The Creative Hub AI demo is the headline
            AI talk; adjacent experience rounds out the record.
          </p>
        </div>

        <div className="grid md:grid-cols-2 gap-6 mb-16">
          {/* Video placeholder */}
          <div className="aspect-video bg-dark-700 rounded-2xl border border-white/5 overflow-hidden flex items-center justify-center">
            <div className="text-center p-4">
              <svg className="w-12 h-12 mx-auto text-silver-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <p className="text-silver-500 text-sm">[TALK VIDEO — PLACEHOLDER]</p>
              <p className="text-silver-600 text-xs mt-1">Replace with YouTube/Vimeo embed</p>
            </div>
          </div>

          {/* Photo placeholder */}
          <div className="aspect-video bg-dark-700 rounded-2xl border border-white/5 overflow-hidden flex items-center justify-center">
            <div className="text-center p-4">
              <svg className="w-12 h-12 mx-auto text-silver-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              <p className="text-silver-500 text-sm">[EVENT PHOTO — PLACEHOLDER]</p>
              <p className="text-silver-600 text-xs mt-1">Replace with event photography</p>
            </div>
          </div>
        </div>

        <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
          {talks.map(talk => (
            <div
              key={talk.title}
              className="p-5 rounded-xl bg-dark-700/50 border border-white/5 hover:border-gold-400/20 transition-all duration-300"
            >
              <div className="flex items-start justify-between mb-3">
                <span className="text-xs text-silver-500 font-mono">{talk.date}</span>
                {talk.type === 'featured' && (
                  <span className="text-[10px] uppercase tracking-wider text-gold-400 font-medium px-2 py-0.5 rounded-full border border-gold-400/30">
                    Featured
                  </span>
                )}
              </div>
              <h3 className="text-base font-display font-semibold text-white mb-1">
                {talk.title}
              </h3>
              <p className="text-xs text-silver-500 mb-2">{talk.subtitle}</p>
              <p className="text-xs text-silver-400 leading-relaxed mb-3">
                {talk.description}
              </p>
              <div className="flex flex-wrap gap-1.5">
                {talk.tags.map(tag => (
                  <span
                    key={tag}
                    className="text-[10px] text-silver-500 bg-dark-600 px-2 py-0.5 rounded-full"
                  >
                    {tag}
                  </span>
                ))}
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  )
}
