<?php

namespace AuPenDuick\Controller;

use AuPenDuick\Model\CompanyTextManager;
use AuPenDuick\Model\CompanyPictureManager;
use AuPenDuick\Model\CategoryManager;
use AuPenDuick\Model\Food;
use AuPenDuick\Model\FoodManager;
use AuPenDuick\Model\TypeManager;

class AdminController extends Controller
{
    const MaxSize = 1000000;
    const LimitPicture = 4;

    public function adminAction()
    {
        return $this->twig->render('Admin/admin.html.twig');
    }

    public function menuAction()
    {
        // Récupération des photos de la carte
        $companyPicturesManager = new CompanyPictureManager();
        $pictures = $companyPicturesManager->findAll();
        foreach ($pictures as $picture) {
            $listPictures[] = $picture;
        }

        // Récupération de tous les types (salé,sucré)
        $typeManager = new TypeManager();
        $types = $typeManager->findAllType();

        // Récupération des catégories en fonction de l'id du type
        $menus = [];
        foreach ($types as $type) {
            $categoryManager = new CategoryManager();
            $categories = $categoryManager->findByType($type->getId());

            // Récupération des crêpes en fonction de l'id de la catégorie
            foreach ($categories as $category) {
                $foodManager = new FoodManager();
                $foods = $foodManager->findByCategory($category->getId());

                // Tableau des crêpes en fonction du type et de la catégorie
                foreach ($foods as $food) {
                    $menus[$type->getConsistency()][$category->getName()][] = $food;
                }
            }
            if (!empty($_POST['id'])) {
                $foodManager = new FoodManager();
                $food = $foodManager->findOneFood($_POST['id']);
                $foodManager->deleteFood($food);
                header('Location: index.php?route=menuAdmin');
            }
        }

        return $this->twig->render('Admin/menuAdmin.html.twig', [
            'menus' => $menus,
            'pictures' => $listPictures,
        ]);
    }

    public function updateFoodAction(){
        return $this->twig->render('Admin/updateFood.html.twig');
    }

    public function addTypeAction(){
        return $this->twig->render('Admin/addType.html.twig');
    }

    public function addCategoryAction(){
        return $this->twig->render('Admin/addCategory.html.twig');
    }

    public function addFoodAction()
    {
        // récupérer $_POST et traiter
        $errors = [];

        // creation d'un objet person vide
        $crepe = new Food();

        if (!empty($_POST)) {

            // traitement des erreurs éventuelles
            $crepe->setTitle($_POST['title']);
            $crepe->setDescription($_POST['description']);
            $crepe->setPrice($_POST['price']);
            $crepe->setCategoryId($_POST['category']);

            if (empty($_POST['title'])) {
                $errors[] = 'Title is required';
            } elseif (strlen($_POST['title']) < 3) {
                $errors[] = 'Title should be at least 3 characters';
            }
            if (empty($_POST['description'])) {
                $errors[] = 'Description is required';
            }

            if (empty($_POST['price'])) {
                $errors[] = 'Price is required';
            }

            // si pas d'erreur, insert en bdd
            if (empty($errors)) {

                $foodManager = new FoodManager();
                $foodManager->insertFood($crepe);

                header('Location: index.php?route=menuAdmin');
            }
        }

        $categoryManager = new CategoryManager();
        $categories = $categoryManager->findAll();

        return $this->twig->render('Admin/addFood.html.twig', [
            'errors' => $errors,
            'categories' => $categories,
            'crepe' => $crepe,
        ]);
    }

    public function deletePictureAction()
    {
        $uploadInfo = '';

        // Vérification si demande de delete
        if (!empty($_POST['delete'])) {

            // Récup id via form
            $id = (int) $_POST['delete'];

            // appel Class
            $upload = new CompanyPictureManager();

            // Vérification de la présence du fichier
            $name = $upload->findOne($id);
            if (file_exists('pictures/upload/' . $name->getName())) {

                // Delete
                $upload->deleteById($id);
                unlink('pictures/upload/' . $name->getName());

                // Message d'informations
                $uploadInfo = 'Suppression de l\'image réussie';

            } else {
                // Message d'informations
                $uploadInfo = 'L\'image n\' pas été supprimée à car elle n\'existe pas';
            }
        }

        // Récupération de la liste des images
        $companyPictureManager = new CompanyPictureManager();
        $listPictures = $companyPictureManager->findAll();

        return $this->twig->render('Admin/deletePicture.html.twig', [
            'uploadInfo' => $uploadInfo,
            'listPictures' => $listPictures,
        ]);
    }

    public function addPictureAction()
    {
        // Initialise
        $info = '';
        $companyPictureManager = new CompanyPictureManager();

        // Maximum 4 images
        if ($companyPictureManager->countAll() >= self::LimitPicture) {
            $info = 'Vous ne pouvez mettre que '.self::LimitPicture.' photos sur la carte';
        }

        if (!empty($_FILES['upload']) && $info == '') {

            // Nettoyage du name
            $_FILES['upload']['name'] = uniqid() . $_FILES['upload']['name'];

            // Vérification du type
            $extensions_valids = array('jpg', 'jpeg', 'gif', 'png');
            $extension_upload = pathinfo($_FILES['upload']['name'], PATHINFO_EXTENSION);

            if (!in_array($extension_upload, $extensions_valids)) {
                $info = 'le fichier n\'est pas du bon format';

            // Vérification de la taille
            } elseif ($_FILES['upload']['size'] >= self::MaxSize)  {
                $info = 'la taille de l\'image est trop lourde';

            // Tout est bon
            } else {

                // Insert fichier upload
                move_uploaded_file($_FILES['upload']['tmp_name'], 'pictures/upload/' . $_FILES['upload']['name']);

                // Insert Bdd via Model
                $companyPictureManager->addOne($_FILES['upload']['name']);

                // Message
                $info = 'L\'image a bien été ajoutée';
            }
        }

        return $this->twig->render('Admin/addPicture.html.twig', [
            'error' => $info,
        ]);
    }

    public function updateTextAction(){
        return $this->twig->render('Admin/updateText.html.twig');
    }
}