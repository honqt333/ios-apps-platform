# Frontend (Next.js 15)

Web interface for the iOS Apps Distribution Platform.

## 📋 Stack

- Next.js 15 (App Router)
- React 19
- TypeScript 5
- Tailwind CSS 3
- next-intl (i18n)
- Zustand (state)
- Zod + react-hook-form (forms)
- Axios (HTTP)
- Radix UI primitives

## 🚀 Quick start

```bash
npm install
cp .env.example .env
npm run dev
```

App: `http://localhost:3000`

## 📂 Structure

```
src/
├── app/
│   ├── [locale]/
│   │   ├── layout.tsx
│   │   ├── page.tsx
│   │   ├── apps/
│   │   ├── categories/
│   │   ├── search/
│   │   ├── auth/
│   │   └── admin/
│   ├── globals.css
│   └── page.tsx
├── components/
│   ├── ui/
│   ├── apps/
│   ├── admin/
│   ├── layout/
│   ├── shared/
│   └── providers/
├── hooks/
├── lib/
│   ├── api.ts
│   └── utils.ts
├── services/
├── stores/
├── types/
└── i18n/
    ├── request.ts
    ├── routing.ts
    ├── middleware.ts
    └── locales/
        ├── en.json
        └── ar.json
```

## 🌐 i18n

Default locale: `en`. Arabic (`ar`) supported with full RTL.

URLs are prefixed: `/en/apps`, `/ar/apps`.

To add a new locale:
1. Add it to `src/i18n/request.ts` (locales array)
2. Add a new JSON file in `src/i18n/locales/`
3. Add Tailwind RTL tweaks if needed

## 🌓 Theming

Dark/light/system via `ThemeProvider`. Preference persisted in `localStorage`.

## 🧪 Build

```bash
npm run build
npm run start
```
