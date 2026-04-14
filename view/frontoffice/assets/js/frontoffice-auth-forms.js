(function () {
    'use strict';

    const emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const telRe = /^[0-9]{8,15}$/;
    const dateRe = /^\d{4}-\d{2}-\d{2}$/;

    function apiUrl(action) {
        var endpoint = 'index.php?action=' + encodeURIComponent(action);
        // If opened directly as file://, force requests to Apache localhost.
        if (window.location.protocol === 'file:') {
            endpoint = 'http://localhost/projectttttt/digiwork-hub/view/frontoffice/index.php?action=' + encodeURIComponent(action);
        }
        return endpoint;
    }

    function setAlert(el, message, kind) {
        if (!el) {
            return;
        }
        el.textContent = message || '';
        el.className = 'alert-message' + (message ? (kind === 'ok' ? ' alert-success' : ' alert-error') : '');
        el.style.display = message ? 'block' : 'none';
    }

    window.openModal = function (id) {
        const overlay = document.getElementById(id);
        if (overlay) {
            overlay.style.display = 'flex';
        }
    };

    window.closeModal = function (id) {
        const overlay = document.getElementById(id);
        if (overlay) {
            overlay.style.display = 'none';
        }
    };

    window.switchToSignup = function () {
        window.closeModal('loginModal');
        window.openModal('signupModal');
    };

    window.switchToLogin = function () {
        window.closeModal('signupModal');
        window.openModal('loginModal');
    };

    let selectedRole = 'candidat';

    window.selectRole = function (role) {
        selectedRole = role;
        var roleInput = document.getElementById('signupRole');
        if (roleInput) {
            roleInput.value = role;
        }
        document.querySelectorAll('.role-option').forEach(function (el) {
            el.classList.toggle('selected', el.getAttribute('data-role') === role);
        });
        document.querySelectorAll('.dynamic-fields').forEach(function (el) {
            el.classList.remove('active');
        });
        var map = {
            candidat: 'candidatFields',
            entreprise: 'entrepriseFields',
            sponsor: 'sponsorFields',
            admin: 'adminFields',
        };
        var blockId = map[role];
        if (blockId) {
            var block = document.getElementById(blockId);
            if (block) {
                block.classList.add('active');
            }
        }
    };

    function validateLogin(email, password) {
        if (!emailRe.test(email.trim())) {
            return 'Email invalide.';
        }
        if (password.length === 0) {
            return 'Mot de passe obligatoire.';
        }
        return '';
    }

    function validateSignupBase(email, tel, password, confirm) {
        if (!emailRe.test(email.trim())) {
            return 'Email invalide.';
        }
        if (!telRe.test(tel.trim())) {
            return 'Telephone invalide (8 a 15 chiffres).';
        }
        if (password.length < 6) {
            return 'Mot de passe: minimum 6 caracteres.';
        }
        if (password !== confirm) {
            return 'Les mots de passe ne correspondent pas.';
        }
        return '';
    }

    function validateSignupRole() {
        function nonEmpty(id) {
            var el = document.getElementById(id);
            return el && el.value.trim().length > 0;
        }

        if (selectedRole === 'candidat') {
            if (!nonEmpty('candidatNom') || !nonEmpty('candidatPrenom')) {
                return 'Nom et prenom obligatoires pour le candidat.';
            }
            var ddn = document.getElementById('candidatDdn');
            if (!ddn || !dateRe.test(ddn.value.trim())) {
                return 'Date de naissance au format AAAA-MM-JJ.';
            }
        } else if (selectedRole === 'entreprise') {
            if (!nonEmpty('entrepriseNom') || !nonEmpty('entrepriseAdresse')) {
                return 'Nom et adresse de l\'entreprise obligatoires.';
            }
        } else if (selectedRole === 'sponsor') {
            if (!nonEmpty('sponsorNom') || !nonEmpty('sponsorPrenom') || !nonEmpty('sponsorSociete')) {
                return 'Tous les champs sponsor sont obligatoires.';
            }
        } else if (selectedRole === 'admin') {
            if (!nonEmpty('adminNom') || !nonEmpty('adminPrenom') || !nonEmpty('adminCode')) {
                return 'Nom, prenom et code admin obligatoires.';
            }
            var adminDdn = document.getElementById('adminDdn');
            if (!adminDdn || !dateRe.test(adminDdn.value.trim())) {
                return 'Date de naissance admin au format AAAA-MM-JJ.';
            }
        }
        return '';
    }

    function applyLoggedInState(user) {
        var authButtons = document.getElementById('authButtons');
        var signupButton = document.getElementById('signupButton');
        if (!authButtons) {
            return;
        }
        while (authButtons.firstChild) {
            authButtons.removeChild(authButtons.firstChild);
        }
        var label = document.createElement('span');
        label.className = 'page-scroll';
        label.style.cursor = 'default';
        label.textContent = user && user.email ? user.email + (user.role ? ' (' + user.role + ')' : '') : 'Compte';
        authButtons.appendChild(label);
        var space = document.createTextNode(' ');
        authButtons.appendChild(space);
        var logout = document.createElement('a');
        logout.className = 'page-scroll';
        logout.style.cursor = 'pointer';
        logout.id = 'logoutLink';
        logout.textContent = 'Deconnexion';
        logout.addEventListener('click', function () {
            sessionStorage.removeItem('digiworkUser');
            window.location.reload();
        });
        authButtons.appendChild(logout);
        if (signupButton) {
            signupButton.style.display = 'none';
        }
    }

    function ensureContactFeedback() {
        var form = document.getElementById('contactForm');
        if (!form) {
            return null;
        }
        var el = document.getElementById('contactFormFeedback');
        if (!el) {
            el = document.createElement('div');
            el.id = 'contactFormFeedback';
            el.setAttribute('role', 'status');
            el.style.cssText = 'display:none;margin-bottom:12px;padding:10px 12px;border-radius:8px;font-size:14px;';
            form.insertBefore(el, form.firstChild);
        }
        return el;
    }

    function showContactFeedback(text, isError) {
        var el = ensureContactFeedback();
        if (!el) {
            return;
        }
        el.textContent = text;
        el.style.display = text ? 'block' : 'none';
        el.style.background = isError ? '#fee' : '#efe';
        el.style.color = isError ? '#c00' : '#060';
        el.style.border = '1px solid ' + (isError ? '#fcc' : '#cfc');
    }

    function validateContact(name, email, message) {
        if (name.trim().length < 2) {
            return 'Nom trop court (minimum 2 caracteres).';
        }
        if (!emailRe.test(email.trim())) {
            return 'Email invalide.';
        }
        if (message.trim().length < 10) {
            return 'Message trop court (minimum 10 caracteres).';
        }
        return '';
    }

    function parseApiResponse(response) {
        return response.text().then(function (raw) {
            try {
                return JSON.parse(raw);
            } catch (e) {
                var cleaned = (raw || '').replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
                return {
                    success: false,
                    message: cleaned || 'Reponse serveur invalide.',
                };
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        window.selectRole('candidat');

        try {
            var raw = sessionStorage.getItem('digiworkUser');
            if (raw) {
                var u = JSON.parse(raw);
                if (u && u.email) {
                    applyLoggedInState(u);
                }
            }
        } catch (e) {
            sessionStorage.removeItem('digiworkUser');
        }

        var loginForm = document.getElementById('loginForm');
        if (loginForm) {
            loginForm.addEventListener('submit', function (ev) {
                ev.preventDefault();
                var alertEl = document.getElementById('loginAlert');
                var email = document.getElementById('loginEmail').value;
                var password = document.getElementById('loginPassword').value;
                var err = validateLogin(email, password);
                if (err) {
                    setAlert(alertEl, err, 'err');
                    return;
                }
                setAlert(alertEl, '', 'err');
                fetch(apiUrl('login'), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json; charset=utf-8' },
                    body: JSON.stringify({ email: email.trim(), password: password }),
                })
                    .then(parseApiResponse)
                    .then(function (data) {
                        if (data.success) {
                            setAlert(alertEl, data.message || 'Connexion reussie.', 'ok');
                            if (data.user) {
                                sessionStorage.setItem('digiworkUser', JSON.stringify(data.user));
                            }
                            window.closeModal('loginModal');
                            applyLoggedInState(data.user);
                        } else {
                            setAlert(alertEl, data.message || 'Echec de la connexion.', 'err');
                        }
                    })
                    .catch(function () {
                        setAlert(alertEl, 'Erreur reseau ou serveur.', 'err');
                    });
            });
        }

        var signupForm = document.getElementById('signupForm');
        if (signupForm) {
            signupForm.addEventListener('submit', function (ev) {
                ev.preventDefault();
                var alertEl = document.getElementById('signupAlert');
                var email = document.getElementById('signupEmail').value;
                var tel = document.getElementById('signupTel').value;
                var password = document.getElementById('signupPassword').value;
                var confirm = document.getElementById('signupConfirmPassword').value;
                var baseErr = validateSignupBase(email, tel, password, confirm);
                if (baseErr) {
                    setAlert(alertEl, baseErr, 'err');
                    return;
                }
                var roleErr = validateSignupRole();
                if (roleErr) {
                    setAlert(alertEl, roleErr, 'err');
                    return;
                }
                setAlert(alertEl, '', 'err');
                fetch(apiUrl('signup'), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json; charset=utf-8' },
                    body: JSON.stringify({
                        email: email.trim(),
                        tel: tel.trim(),
                        password: password,
                        role: selectedRole,
                    }),
                })
                    .then(parseApiResponse)
                    .then(function (data) {
                        if (data.success) {
                            setAlert(alertEl, data.message || 'Inscription reussie.', 'ok');
                            window.closeModal('signupModal');
                            window.openModal('loginModal');
                            setAlert(document.getElementById('loginAlert'), 'Vous pouvez vous connecter.', 'ok');
                        } else {
                            setAlert(alertEl, data.message || 'Inscription refusee.', 'err');
                        }
                    })
                    .catch(function () {
                        setAlert(alertEl, 'Erreur reseau ou serveur.', 'err');
                    });
            });
        }

        var contactForm = document.getElementById('contactForm');
        if (contactForm) {
            contactForm.addEventListener('submit', function (ev) {
                ev.preventDefault();
                var name = document.getElementById('name');
                var email = document.getElementById('email');
                var message = document.getElementById('message');
                if (!name || !email || !message) {
                    return;
                }
                var msg = validateContact(name.value, email.value, message.value);
                if (msg) {
                    showContactFeedback(msg, true);
                    return;
                }
                showContactFeedback('Message valide (aucun envoi serveur configure pour ce formulaire).', false);
            });
        }
    });
})();
