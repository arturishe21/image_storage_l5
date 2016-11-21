"use strict";

var ImageStorage = {

    images_page: 1,
    is_last_page: false,
    is_images_loading: false,

    init: function()
    {
        ImageStorage.initEvents()
        //fixme jQuery('.select2-hidden-accessible').hide();  TableBuilder.initSelect2Hider();

    },

    initEvents: function()
    {
        ImageStorage.initSelectBoxes();

        ImageStorage.initScrollEndlessEvent();

        ImageStorage.initEditClickEvent();

        ImageStorage.initDatePickers();
    },

    initSelectBoxes: function()
    {
        $('select.image-storage-select').select2("destroy").select2();
    },

    initScrollEndlessEvent: function()
    {
        $(document).scroll(function() {
            if ($(document).scrollTop() + $(window).height() == $(document).height()) {
                if ($('.image-storage-container.images-container').length) {
                    ImageStorage.loadMoreImages();
                }
            }
        });
    },

    initEditClickEvent: function()
    {
        $('.superbox-list').unbind("click");

        $('.superbox-list').click(function(e) {

            if (e.ctrlKey) {
                //fixme close popup if opened
                $(this).toggleClass('selected')

                ImageStorage.checkSelectedImages();
            }else{
                //fixme remove popup on second click
                ImageStorage.getImageEditForm($(this));
            }
        })
    },

    initPopupClicks: function()
    {
        $('.superbox-close').click(function() {
            ImageStorage.closeSuperBoxPopup();
        });
        jQuery('.j-images-storage').on('click', function() {
            jQuery('.select22').select2("close");
            jQuery('.select2-hidden-accessible').hide();
        });
    },

    initDatePickers: function()
    {
        $('.datepicker').datepicker({
            changeMonth: true,
            prevText: '<i class="fa fa-chevron-left"></i>',
            nextText: '<i class="fa fa-chevron-right"></i>',
            dateFormat: "dd-mm-yy",
            //showButtonPanel: true,
            regional: ["ru"],
            onClose: function (selectedDate) {}
        });
    },

    //images
    resetUploadPreloader: function(button)
    {
        $(button).hide().parent().parent().hide();
        $('.image-storage-progress-fail,.image-storage-progress-success').css('width', '0%');
        $('.image-storage-upload-success, .image-storage-upload-fail, .image-storage-upload-upload, .image-storage-upload-total').text("0");
    },

    loadMoreImages: function()
    {
        if (ImageStorage.is_images_loading) {
            return;
        }
        TableBuilder.showPreloader();
        ImageStorage.is_images_loading = true;

        var data = $('#image-storage-search-form').serializeArray();

        data.push({ name: 'page', value: ImageStorage.images_page});

        jQuery.ajax({
            type: "POST",
            url: "/admin/image_storage/images/load_more_images",
            data: data,
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    ImageStorage.images_page = ImageStorage.images_page + 1;
                    $('.superbox').append(response.html);
                    ImageStorage.initEditClickEvent();
                    ImageStorage.is_images_loading = false;
                    TableBuilder.hidePreloader();
                } else {
                    TableBuilder.hidePreloader();
                    TableBuilder.showErrorNotification('Неудалось загрузить изображения...');
                }
            }
        });

    }, // end loadMoreImages

    uploadImage: function(context)
    {
        var imgTotal = context.files.length;
        var imgCount        = 0;
        var imgFailCount    = 0;
        var imgSuccessCount = 0;
        var percentageMod     = 100 / imgTotal;
        var failPercentage    = 0;
        var successPercentage = 0;

        var imageIdsArray = [];

        var $fog = $('.image-storage-process-popup').show();
        $fog.find('.image-storage-upload-total').text(imgTotal);

        $fog.find('.image-storage-progress-success').css('width', '1%');

        for (var x = 0; x < imgTotal; x++) {
            var data = new FormData();
            data.append("image", context.files[x]);

            jQuery.ajax({
                data: data,
                type: "POST",
                url: "/admin/image_storage/images/upload",
                cache: false,
                contentType: false,
                processData: false,
                success: function(response) {
                    imgCount = imgCount + 1;

                    if (response.status) {
                        $('.superbox').prepend(response.html);
                        ImageStorage.initEditClickEvent();
                        imgSuccessCount = imgSuccessCount + 1;
                        successPercentage = successPercentage + percentageMod;

                        imageIdsArray.push(response.id);

                        $fog.find('.image-storage-upload-upload').text(imgCount);
                        $fog.find('.image-storage-upload-success').text(imgSuccessCount);
                        $fog.find('.image-storage-progress-success').css('width', successPercentage +'%');

                    } else {
                        imgFailCount = imgFailCount + 1;
                        failPercentage = successPercentage + failPercentage;
                        var failWidth  = successPercentage + failPercentage;
                        $fog.find('.image-storage-progress-fail').css('width', failWidth +'%');
                        $fog.find('.image-storage-upload-fail').text(imgFailCount);

                        if (response.message){
                            TableBuilder.showErrorNotification(response.message);
                        }else{
                            TableBuilder.showErrorNotification("Ошибка при загрузке изображения");
                        }
                    }

                    if (imgCount == imgTotal) {
                        $fog.find('.image-storage-upload-finish-btn').show();
                        ImageStorage.sendOptimizeImageRequest(imageIdsArray);

                    }
                }
            });
        }
        $("#upload-image-storage-input").val("");
    }, // end uploadFile

    getImageEditForm: function(context)
    {
        var $this = $(context);
        var currentImg = $this.find('.superbox-img');

        jQuery.ajax({
            type: "POST",
            url: "/admin/image_storage/images/get_image_form",
            dataType: 'json',
            data: {
                id: currentImg.data('id'),
            },
            success: function(response) {
                if (response.status) {

                    $('.superbox-list').removeClass('active');
                    $this.addClass('active');

                    ImageStorage.openFormPopup(context,currentImg, response.html);

                    ImageStorage.initSelectBoxes();

                    ImageStorage.initPopupClicks();

                    ImageStorage.replaceSrcImageForm();

                } else {
                    TableBuilder.showErrorNotification('Что-то пошло не так');
                }
            }
        });
    },

    openFormPopup: function(context,currentImg,html)
    {
        $('.superbox-show').remove();

        $(html).insertAfter(context).css('display', 'block');

        var superbox =  $('.superbox-show');

        $('html, body').animate({
            scrollTop:superbox.position().top - currentImg.width()
        }, 'medium');

    },

    closeSuperBoxPopup: function()
    {
        $('.superbox-list').removeClass('active');
        $('.superbox-current-img').animate({opacity: 0}, 200, function() {
            $('.superbox-show').slideUp();
        });

    },

    replaceSrcImageForm: function()
    {
        $('#image-storage-images-sizes-tabs li').click(function(){
            var element = $(this).find("a").attr('href');
            var img = ($("#image-storage-images-sizes-tabs-content").find(element).find("img"));

            if(typeof img.attr('real-src') !== typeof undefined && img.attr('real-src') !== false)
            {
                img.attr('src',img.attr("real-src"));
                img.removeAttr("real-src")
            }
        });
    },

    replaceSingleImage: function(context, size, idImage)
    {
        var data = new FormData();
        data.append("image", context.files[0]);
        data.append('size', size);
        data.append('id', idImage);

        jQuery.ajax({
            data: data,
            type: "POST",
            url: "/admin/image_storage/images/replace_single_image",
            cache: false,
            contentType: false,
            processData: false,
            success: function(response) {
                if (response.status) {
                    $(context).parents(".tab-pane.active").find('.superbox-current-img').prop('src', response.src);
                    ImageStorage.sendOptimizeImageRequest(idImage, size);
                } else {
                    if (response.message){
                        TableBuilder.showErrorNotification(response.message);
                    }else{
                        TableBuilder.showErrorNotification("Ошибка при загрузке изображения");
                    }
                }
            }
        });
    },

    doSearch: function()
    {
        var data = $('#image-storage-search-form').serializeArray();
        TableBuilder.showPreloader();

        jQuery.ajax({
            type: "POST",
            url: "/admin/image_storage/images/search_images",
            data: data,
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    $('.image-storage-container').html(response.html);
                    ImageStorage.images_page = 1;
                    ImageStorage.init();
                    TableBuilder.hidePreloader();
                } else {
                    TableBuilder.showErrorNotification('Что-то пошло не так');
                    TableBuilder.hidePreloader();

                }
            }
        });
    },

    doResetFilters: function()
    {
        $('#image-storage-search-form')[0].reset();
        ImageStorage.doSearch();
    },

    deleteImage: function(idImage)
    {
        jQuery.SmartMessageBox({
            title : "Удалить изображение?",
            content : "Эту операцию нельзя будет отменить.",
            buttons : '[Нет][Да]'
        }, function(ButtonPressed) {
            if (ButtonPressed === "Да") {
                jQuery.ajax({
                    type: "POST",
                    url: "/admin/image_storage/images/delete_image",
                    data: { id: idImage },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status) {
                            $('.superbox .superbox-show').remove();
                            $('.superbox').find(".image-storage-img[data-id='" + idImage + "']").parent().remove();

                            TableBuilder.showSuccessNotification('Изображение удалено');
                        } else {
                            TableBuilder.showErrorNotification('Что-то пошло не так');
                        }
                    }
                });
            }
        });
    }, // end deleteImage

    saveImageInfo: function(idImage)
    {
        var data = $('#imgInfoBox-form').serializeArray();
        data.push({ name: 'id', value: idImage });

        jQuery.ajax({
            type: "POST",
            url: "/admin/image_storage/images/save_image_info",
            data: data,
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    TableBuilder.showSuccessNotification('Сохранено');
                } else {
                    TableBuilder.showErrorNotification('Что-то пошло не так');
                }
            }
        });


    }, // end saveImageInfo

    getSelectedImages: function()
    {
        var images = $('.superbox-list.selected img'),
            imagesArray = [];

        images.each(function() {
            imagesArray.push($(this).data('id'));
        });

        return imagesArray;

    },

    checkSelectedImages: function()
    {
        var imagesArray = ImageStorage.getSelectedImages();

        if (imagesArray.length) {
            $('.image-storage-image-operations').show();
        } else {
            $('.image-storage-image-operations').hide();
            $('form[name=image-storage-image-operations-form]')[0].reset();
        }
    }, // end checkSelectedImages

    sendOptimizeImageRequest: function (id, size)
    {
        jQuery.ajax({
            type: "POST",
            url: "/admin/image_storage/images/optimize_image",
            data: {
                id:    id,
                size:  size,
            },
            dataType: 'json',
/*            success: function(response) {
                if (response.status) {
                    TableBuilder.showSuccessNotification('Изображение успешно оптимизированно');
                } else {
                    TableBuilder.showErrorNotification('Что-то пошло не так при оптимизации изображения');
                }
            }
*/
        });
    },

    //galleries
    doSearchGalleries: function()
    {
        var data = $('#image-storage-search-form').serializeArray();
        TableBuilder.showPreloader();

        jQuery.ajax({
            type: "POST",
            url: "/admin/image_storage/galleries",
            data: data,
            success : function(response) {
                    $('#content_admin').html(response);
                    ImageStorage.init();
                    TableBuilder.hidePreloader()
            }
        });
    },

    doResetFiltersGallery: function()
    {
        TableBuilder.showPreloader();

        jQuery.ajax({
            type: "POST",
            url: "/admin/image_storage/galleries",
            data: {forget_filters: true},
            success : function(response) {
                $('#content_admin').html(response);
                ImageStorage.init();
                TableBuilder.hidePreloader()
            }
        });
    },

    getGalleryEditForm: function(id)
    {
        id =   id || null;

        TableBuilder.showPreloader();

        jQuery.ajax({
            type: "POST",
            url: "/admin/image_storage/galleries/get_gallery_form",
            dataType: 'json',
            data: { id: id },
            success: function(response) {
                if (response.status) {
                    $("#modal_form").modal('show');
                    $("#modal_form .modal-content").html(response.html);
                    TableBuilder.hidePreloader();
                    ImageStorage.initSortable();
                    ImageStorage.initSelectBoxes();
                } else {
                    TableBuilder.hidePreloader();
                    TableBuilder.showErrorNotification('Что-то пошло не так');
                }
            }
        });
    },

    deleteGallery: function(id)
    {
        jQuery.SmartMessageBox({
            title : "Удалить галерею?",
            content : "Эту операцию нельзя будет отменить.",
            buttons : '[Нет][Да]'
        }, function(ButtonPressed) {
            if (ButtonPressed === "Да") {
                jQuery.ajax({
                    type: "POST",
                    url: "/admin/image_storage/galleries/delete_gallery",
                    data: { id: id },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status) {
                            $('.tr_'+id).remove();
                            //fixme hide modal gallery popup
                            if($(".modal-body.row").length){
                                $("button.close").click();
                            }
                            TableBuilder.showSuccessNotification('Галерея удалена');
                        } else {
                            TableBuilder.showErrorNotification('Что-то пошло не так');
                        }
                    }
                });
            }
        });
    }, // end deleteImage

    initSortable: function()
    {
        var $sortable = $('.image-storage-sortable');
        $sortable.sortable(
            {
                items: "> li",
                context: this,
                update: function() {
                    ImageStorage.onChangeGalleryImagesOrder();
                },
                create: function(event, ui) {
                    $('.image-storage-sortable-item').click(function(e) {
                        if (e.ctrlKey) {
                            ImageStorage.setGalleryPreviewImage(this);
                        }
                    });
                },
            }
        );
        $sortable.disableSelection();
    },

    saveGalleryInfo: function(id)
    {
        var data = $('#imgInfoBox-form-gallery').serializeArray();
        data.push({ name: 'id', value: id });

        jQuery.ajax({
            type: "POST",
            url: "/admin/image_storage/galleries/save_gallery_info",
            data: data,
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    TableBuilder.showSuccessNotification('Сохранено');
                    //fixme hide modal gallery popup
                    if($(".modal-body.row").length){
                        $("button.close").click();
                    }
                } else {
                    TableBuilder.showErrorNotification('Что-то пошло не так');
                }
            }
        });


    }, // end saveImageInfo

    onChangeGalleryImagesOrder: function()
    {
        var images = $('.image-storage-sortable').sortable('toArray');
        //fixme hidden input? find better decision
        var idGallery = $('[name=gallery_id]').val();

        jQuery.ajax({
            type: "POST",
            url: "/admin/image_storage/galleries/change_image_order",
            data: { images: images, idGallery:idGallery  },
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    TableBuilder.showSuccessNotification('Порядок следования изменен');
                } else {
                    TableBuilder.showErrorNotification('Что-то пошло не так');
                }
            }
        });
    },

    deleteGalleryImageRelation: function(idImage, idGallery)
    {
        jQuery.SmartMessageBox({
            title : "Удалить изображение из галереи?",
            content : "Эту операцию нельзя будет отменить.",
            buttons : '[Нет][Да]'
        }, function(ButtonPressed) {
            if (ButtonPressed === "Да") {
                jQuery.ajax({
                    type: "POST",
                    url: "/admin/image_storage/galleries/delete_image_relation",
                    data: { idImage: idImage, idGallery:idGallery  },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status) {
                            $('li#'+idImage).remove();
                            $(".image-storage-sortable").sortable("refresh");
                            TableBuilder.showSuccessNotification('Изображение удалено из галереи');
                        } else {
                            TableBuilder.showErrorNotification('Что-то пошло не так');
                        }
                    }
                });
            }
        });
    }, // end deleteGalleryImageRelation

    setGalleryPreviewImage: function(image)
    {
        var idImage = $(image).attr('id');
        //fixme hidden input? find better decision
        var idGallery = $('[name=gallery_id]').val();

        jQuery.ajax({
            type: "POST",
            url: "/admin/image_storage/galleries/set_gallery_image_preview",
            data: { idImage: idImage, idGallery:idGallery  },
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    $(".image-storage-sortable-item").removeClass('preview');
                    $(image).addClass('preview');
                    TableBuilder.showSuccessNotification('Превью галереи успешно установлено');
                } else {
                    TableBuilder.showErrorNotification('Что-то пошло не так');
                }
            }
        });
    }, // end deleteGalleryImageRelation

    createGalleryWithImages: function ()
    {

        var galleryName = $('form[name="image-storage-image-operations-form"] input[name="gallery_name"]').val().trim();

        if (!galleryName) {
            TableBuilder.showErrorNotification('Введите название галереи для создания');
            return false;
        }

        var imagesArray = ImageStorage.getSelectedImages();

        jQuery.ajax({
            type: "POST",
            url: "/admin/image_storage/galleries/create_gallery_with_images",
            data: {
                images:     imagesArray,
                galleryName:   galleryName
            },
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    TableBuilder.showSuccessNotification('Галерея с изображениями успешно создана');
                } else {
                    TableBuilder.showErrorNotification('Что-то пошло не так');
                }
            }
        });

    },

    saveImagesGalleriesRelations: function ()
    {
        var  galleriesArray = $('form[name="image-storage-image-operations-form"] select[name="relations[image-storage-galleries][]"]').val();

        if (!galleriesArray) {
            TableBuilder.showErrorNotification('Выберите галереи для добавления изображений');
            return false;
        }

        var imagesArray = ImageStorage.getSelectedImages();

        jQuery.ajax({
            type: "POST",
            url: "/admin/image_storage/galleries/add_images_to_galleries",
            data: {
                images:     imagesArray,
                galleries:  galleriesArray
            },
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    TableBuilder.showSuccessNotification('Изображения успешно добавлены к галереям');
                } else {
                    TableBuilder.showErrorNotification('Что-то пошло не так');
                }
            }
        });
    },

    saveImagesTagsRelations: function()
    {
        var  tagsArray = $('form[name="image-storage-image-operations-form"] select[name="relations[image-storage-tags][]"]').val();

        if (!tagsArray) {
            TableBuilder.showErrorNotification('Выберите теги для добавления изображений');
            return false;
        }

        var imagesArray = ImageStorage.getSelectedImages();

        jQuery.ajax({
            type: "POST",
            url: "/admin/image_storage/tags/add_images_to_tags",
            data: {
                images:     imagesArray,
                tags:       tagsArray
            },
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    TableBuilder.showSuccessNotification('Изображения добавлены к тегу');
                } else {
                    TableBuilder.showErrorNotification('Что-то пошло не так');
                }
            }
        });
    }, // end saveImagesTagsRelations

    //tags

    doSearchTags: function()
    {
        var data = $('#image-storage-search-form').serializeArray();
        TableBuilder.showPreloader();

        jQuery.ajax({
            type: "POST",
            url: "/admin/image_storage/tags",
            data: data,
            success : function(response) {
                $('#content_admin').html(response);
                ImageStorage.init();
                TableBuilder.hidePreloader()
            }
        });
    },

    doResetFiltersTags: function()
    {
        TableBuilder.showPreloader();

        jQuery.ajax({
            type: "POST",
            url: "/admin/image_storage/tags",
            data: {forget_filters: true},
            success : function(response) {
                $('#content_admin').html(response);
                ImageStorage.init();
                TableBuilder.hidePreloader()
            }
        });
    },


    getTagEditForm: function(id)
    {
        id =   id || null;

        TableBuilder.showPreloader();

        jQuery.ajax({
            type: "POST",
            url: "/admin/image_storage/tags/get_edit_form",
            dataType: 'json',
            data: { id: id },
            success: function(response) {
                if (response.status) {
                    $("#modal_form").modal('show');
                    $("#modal_form .modal-content").html(response.html);
                    TableBuilder.hidePreloader();
                } else {
                    TableBuilder.hidePreloader();
                    TableBuilder.showErrorNotification('Что-то пошло не так');
                }
            }
        });
    },
    deleteTag: function(id)
    {
        jQuery.SmartMessageBox({
            title : "Удалить тэг?",
            content : "Эту операцию нельзя будет отменить.",
            buttons : '[Нет][Да]'
        }, function(ButtonPressed) {
            if (ButtonPressed === "Да") {
                jQuery.ajax({
                    type: "POST",
                    url: "/admin/image_storage/tags/delete_tag",
                    data: { id: id },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status) {
                            $('.tr_'+id).remove();
                            //fixme hide modal gallery popup
                            if($(".modal-body.row").length){
                                $("button.close").click();
                            }
                            TableBuilder.showSuccessNotification('Тэг удалена');
                        } else {
                            TableBuilder.showErrorNotification('Что-то пошло не так');
                        }
                    }
                });
            }
        });
    }, // end deleteImage

    saveTagInfo: function(id)
    {
        var data = $('#imgInfoBox-form-tag').serializeArray();
        data.push({ name: 'id', value: id });

        jQuery.ajax({
            type: "POST",
            url: "/admin/image_storage/tags/save_tag_info",
            data: data,
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    TableBuilder.showSuccessNotification('Сохранено');
                    //fixme hide modal gallery popup
                    if($(".modal-body.row").length){
                        $("button.close").click();
                    }
                } else {
                    TableBuilder.showErrorNotification('Что-то пошло не так');
                }
            }
        });


    }, // end saveImageInfo

};
$(document).ready(function(){
    ImageStorage.init();
});
