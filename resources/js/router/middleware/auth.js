export default function auth({ next, router }) {
  if (!localStorage.getItem('access-token')) {
    next('/login');
    return false;
  }
  next()
}
