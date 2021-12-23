export default function auth({ next, router }) {
  if (!localStorage.getItem('access-token')) {
    return router.push({ name: 'login' });
  }

  return next();
}
