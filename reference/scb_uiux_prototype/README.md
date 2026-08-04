# St. Charles Borromeo UI/UX Prototype

This ZIP contains a responsive, interactive HTML/CSS/JavaScript prototype for:

- Public school website pages
- Filament-oriented Laravel administration screens
- Brand palette and component system
- Original uploaded logo, preserved unchanged
- Optimised copies of the school photographs plus the original files

## Start here

Open `prototype-map.html` in a browser. It links to every screen.

## Brand colours sampled from the uploaded crest

- Crest Plum: `#3D2D3F`
- Crest Crimson: `#B8101E`
- Crest Gold: `#DAAD18`
- Soft Gold: `#E4CD5A`
- Warm Cream: `#F8F5EC`

## Typography direction

- Headings: extra-bold display treatment (`font-weight: 900`)
- Body: neutral, highly readable sans serif
- School name: restrained classic serif accent

For production, self-host a licensed/open font such as Manrope ExtraBold for headings and Source Sans 3 for body copy.

## Laravel / Filament implementation mapping

Public pages should be Blade templates with reusable components and Livewire only where interaction is needed. Admin pages map to Filament Resources:

- Pages + block editor
- News + categories + tags
- Events + calendar fields
- Galleries + media ordering + consent review
- Downloads + categories + versions
- Staff + departments
- Programmes + levels
- Admission enquiries
- Contact messages
- Media library
- Users, roles and permissions
- Settings and audit log

## Important

All textual content, dates, contact details and statistics in this prototype are sample UI copy and must be replaced with approved school content. Child photographs should only be published after confirming consent and safeguarding requirements.
