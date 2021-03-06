<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/1/9
 * Time: 11:29
 */

namespace Admin\Controller;


use Think\Controller;

class BaseController extends Controller
{
    //保存当前模型对象
    protected $model;
    //是否使用post传递的参数
    protected $usePostParams = false;

    public function _initialize()
    {
        $this->model = D(CONTROLLER_NAME);
    }

    public function index()
    {
        //得到查询的关键字
        $keyword = I('get.keyword', '');
        $wheres = array();  //专门用来存放查询的条件
        if (!empty($keyword)) {
            $wheres['obj.name'] = array("like", "{$keyword}%");
        }
        $this->_setWheres($wheres);
//        dump($wheres);
        $pageResult = $this->model->getPageResult($wheres);
//        dump($pageResult);exit;
        //>>3.将数据分配到页面上
        $this->assign($pageResult);

        //>>将当前url地址保存到cookie中
        cookie('__forward__', $_SERVER['REQUEST_URI']);
        $this->assign('meta_title', $this->meta_title);
        //>>4.选择视图页面
        $this->_before_index_view();
        $this->_before_edit_view();
        $this->display('index');
    }

    /**
     * 用于被子类覆盖的钩子方法,子类覆盖它提供查询数据的条件,使用引用传值,改变$wheres条件中的值
     * @param $wheres
     */
    protected function _setWheres(&$wheres)
    {

    }

    /**
     * 根据id将其状态修改为status的值
     * @param $id
     * @param $status  默认值为-1表示删除
     */
    public function changeStatus($id, $status = -1)
    {
        //>>1.创建模型对象
        //>>2.使用模型中的changeStatus修改数据的状态
        $result = $this->model->changeStatus($id, $status);
        if ($result !== false) {
            $this->success('操作成功!', cookie('__forward__'));
        } else {
            $this->error('操作失败!' . show_model_error($this->model));
        }
    }

    public function add()
    {
        if (IS_POST) {
            //>>1.创建模型对象
            //>>2.使用模型中的create方法进行收集数据并且验证
            if ($this->model->create() !== false) {
                //>>3.请求数据添加到数据库中
                if ($this->model->add($this->usePostParams ? I('post.') : '') !== false) {
                    $this->success('添加成功!', U('index'));
                    return;//防止下面的代码继续执行.
                }
            }
            $this->error('操作失败!' . show_model_error($this->model));
        } else {
            $this->assign('meta_title', '添加' . $this->meta_title);
            $this->_before_edit_view();
            $this->display('edit');
        }
    }

    /**
     * 根据id进行编辑
     * @param $id
     */
    public function edit($id)
    {
        if (IS_POST) {
            //>>1.使用模型中的create来接收请求参数
            if ($this->model->create() !== false) {
                //>>2.将请求参数修改到数据库中
                if ($this->model->save($this->usePostParams ? I('post.') : '') !== false) {
                    $this->success('修改成功!', cookie('__forward__'));
                    return;
                }
            }
//            exit;
            $this->error('操作失败!' . show_model_error($this->model));
        } else {
            //>>1.使用模型查询出id对应的数据
            $row = $this->model->find($id);
            //>>2.将数据分配到页面上
            $this->assign($row);
            //>>3.显示edit页面回显数据
            $this->assign('meta_title', '编辑' . $this->meta_title);
            $this->_before_edit_view();
            $this->display('edit');
        }
    }

    protected function _before_edit_view()
    {
    }

    protected function _before_index_view()
    {

    }
}