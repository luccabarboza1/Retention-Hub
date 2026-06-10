export function ThemeScript() {
  const script = `(function(){try{var t=localStorage.getItem('rh-theme');if(t==='dark'||(!t&&window.matchMedia('(prefers-color-scheme: dark)').matches)){document.documentElement.classList.add('dark')}if(localStorage.getItem('rh-sidebar')==='1'){document.documentElement.classList.add('sidebar-collapsed')}}catch(e){}})();`
  return <script dangerouslySetInnerHTML={{ __html: script }} />
}
