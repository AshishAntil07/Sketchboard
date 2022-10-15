<!DOCTYPE html>

<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Sketch Board</title>
    <meta name="description" content="An offline virtual Sketch Board to make your work easy.">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="./board.css">
    <link rel="shortcut icon" href="Pictures/Logo-dark.png" type="image/x-icon">
    
<style>
*{
  margin: 0;
}
body{
  font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
html{
  overflow: hidden;
}

/* Main */

.topBar button{
  height: 30px;
  width: 100px;
  border-radius: 5px;
  background: brown;
  color: white;
  margin-right: 5px;
  outline: none;
  border: none;
}
.topBar button:nth-child(3){
  background: whitesmoke;
  color: black;
}
.topBar button:hover{
  filter: brightness(95%);
}
.pen-width{
  width: 100px;
  height: 25px;
  margin-right: 20px;
  box-sizing: border-box;
  border: none;
  outline: none;
  padding: 5px;
}
.color, .color div div{
  border-radius: 50%;
  margin-right: 10px;
  width: 30px;
  height: 30px;
  transition: transform .3s;
}
.color div div:hover{
  transform: scale(1.1, 1.1);
}
.color{
  background-color: black;
  border: 1px solid white;
}
.container{
  width: 187.5px !important;
  box-sizing: border-box;
  box-shadow: 0px 0px 10px dimgray;
  border-radius: 5px;
  display: none;
  padding: 5px;
  background: rgb(250, 250, 250);
  flex-flow: row wrap;
}
.sideBar div{
  height: 50px;
  width: 50px;
  background-position: center;
  background-repeat: no-repeat;
  background-size: 35px;
}
.sideBar > div:hover{
  background-color: darkgray;
}
.board{
  height: 100%;
  width: 100%;
}

/* Resize Container */

.resizeCont{
  width: 200px;
  height: 200px;
  min-height: 15px;
  min-width: 15px;
  position: absolute;
  background: transparent;
  display: flex;
  justify-items: stretch;
  align-items: stretch;
}
.resizeCont textarea::-webkit-scrollbar{
  display: none;
}
.rotater{
  height: 10px;
  width: 10px;
  border-radius: 5px;
  background: blue;
  position: absolute;
  margin-left: 50%;
  transform: translate(-35%, -200%);
}
.rotater::after{
  content: '';
  background: blue;
  height: 10px;
  width: 1px;
  display: block;
  position: absolute;
  margin-left: 50%;
  transform: translate(-150%, 100%);
}

/* Right Side Bar */

.element{
  height: 120px;
  width: calc(100% - 10px);
  margin-left: 1px;margin-right: 1px;
  margin-bottom: 10px;
}
.element:hover svg{
  outline: 1px solid brown;
}
.elementContainer::-webkit-scrollbar{
  width: 8px;
}
.elementContainer::-webkit-scrollbar-thumb{
  background-color: dimgray;
  border-radius: 5px;
  border: 2px solid rgb(190, 190, 190);
}
.elementContainer::-webkit-scrollbar-thumb:hover{
  background-color: rgb(90, 90, 90);
}
.elementContainer::-webkit-scrollbar-thumb:active{
  background-color: rgb(80, 80, 80);
}
.elementContainer::-webkit-scrollbar-track{
  background: rgb(190, 190, 190);
  border-radius: 5px;
}

/* Points */

.point{
  height: 10px;
  width: 10px;
  background: blue;
  border: 3px solid white;
  border-radius: 50%;
  position: absolute;
  transform: translate(-50%, -50%);
}
.coordinates{
  background: lightgray;
  padding: 5px;
  position: absolute;
  border-radius: 5px;
  transform: translate(10%, -110%);
}

/* pop up buttons */

.popUpButton{
  height: 50px;
  width: 100px;
  display: flex;
  justify-content: center;
  align-items: center;
  cursor: pointer;
  font-size: 20px;
  border-radius: 5px;
}
.popUpButton:first-child{
  margin-right: 20px;
}
.popUpButton:hover{
  filter: brightness(95%);
}

/* Laser Animation */

@keyframes laser{
  100%{opacity: 0; stroke-width: 0;};
}
</style>
  </head>
  <body>
    
    <script>
    // functions
const getStyle = (elem, prop) => Number(getComputedStyle(elem)[prop].replace('px', ''));
const twist = (iter, index) => {
  let newIter=[];
  for(let j=index; j<iter.length; j++) newIter.push(iter[j]);
  for(let k=0; k<index; k++) newIter.push(iter[k]);

  return newIter;
};
const has = (elemList, elem) => {for(let i=0;i<elemList.length;i++) if(elemList[i]==elem) return true; return false}
const resize = (elem, isTextBox) => {
  const container = document.createElement('div');
  container.classList.add('resizeCont');
  isTextBox?container.style.height = 'auto':0;

  const circularDire = ['nw-resize', 'n-resize', 'ne-resize', 'e-resize', 'se-resize', 's-resize', 'sw-resize', 'w-resize'];
  const circularCombos = [
    e=>{resizeFunctions.left(e, container); resizeFunctions.top(e, container)},
    e=>resizeFunctions.top(e, container),
    e=>{resizeFunctions.top(e, container); resizeFunctions.right(e, container)},
    e=>resizeFunctions.right(e, container),
    e=>{resizeFunctions.bottom(e, container); resizeFunctions.right(e, container)},
    e=>resizeFunctions.bottom(e, container),
    e=>{resizeFunctions.left(e, container); resizeFunctions.bottom(e, container)},
    e=>resizeFunctions.left(e, container),
  ]

  let top, left, listeners=[];
  const dragFunc = e => {
    container.style.top = (e.clientY-top)<getStyle(topBar, 'height')
      ?getStyle(topBar, 'height')
      :(e.clientY-top)+'px';
    container.style.left = (e.clientX-left)<getStyle(sideBar, 'width') && right>getStyle(rightSideBar, 'width')
      ?getStyle(sideBar, 'width')
      :(e.clientX-left)+'px';
  }
  container.addEventListener('mousedown', e => {
    if(!(circularElem.includes(e.target))){
      top = e.clientY - getStyle(container, 'top');
      left = e.clientX - getStyle(container, 'left');
      right = e.clientX + getStyle(container, 'width') - window.innerWidth;
      document.addEventListener('mousemove', dragFunc);
      document.addEventListener('mouseup', e => {
        document.removeEventListener('mousemove', dragFunc);
      }, {once:true});
    }
  })

  const rotater = document.createElement('div');
  rotater.classList.add('rotater');
  rotater.onmousedown = e => {
    e.stopPropagation();

    // Cursor

    const cursor = document.createElement('div');
    cursor.setAttribute('style', `
      height: 20px;
      width: 20px;
      background-image: url('Pictures/rotateCursor.png');
      background-repeat: no-repeat;
      background-size: contain;
      background-position: center;
      position: absolute;
      top: ${(e.clientY-(getStyle(cursor, 'height')/2))}px;
      left: ${(e.clientX-(getStyle(cursor, 'width')/2))}px;
    `)
    document.body.append(cursor);
    document.documentElement.style.cursor = 'none';
    document.onmousemove = e => {
      cursor.style.top = (e.clientY-(getStyle(cursor, 'height')/2))+'px';
      cursor.style.left = (e.clientX-(getStyle(cursor, 'width')/2))+'px';

      // Rotation

      const x = container.getBoundingClientRect().x + container.getBoundingClientRect().width/2;
      const y = container.getBoundingClientRect().y + container.getBoundingClientRect().height/2;
      const degree = rotationDegree = (Math.abs((Math.atan2(e.clientX-x, e.clientY-y)*180/Math.PI * -1) + 180));
      container.style.transform = `rotate(${degree}deg)`;
      listeners.forEach(listener => {
        listener.elem.removeEventListener('mousedown', listener);
      })
      const conditions = [
        degree<22.5 || degree>337.5,
        degree<67.5 && degree>22.5,
        degree<112.5 && degree>67.5,
        degree<157.5 && degree>112.5,
        degree<202.5 && degree>157.5,
        degree<247.5 && degree>202.5,
        degree<292.5 && degree>247.5,
        degree<337.5 && degree>292.5
      ]
      listeners=[];
      for(let l=0; l<conditions.length; l++){
        if(conditions[l]){
          const newCircular = twist(circularElem, 8-l);
          newCircular.forEach((btn, index) => {
            btn.style.cursor=circularDire[index];
            function mousedown(){
              document.addEventListener('mousemove', circularCombos[index]);
              document.addEventListener('mouseup', e=>document.removeEventListener('mousemove', circularCombos[index], {once:true}))
            }
            mousedown.elem=btn;
            listeners.push(mousedown);
            btn.addEventListener('mousedown', mousedown);
          })
        }
      }
    }
    document.onmouseup = e => {
      cursor.remove();
      document.onmousemove = () => {}
      document.documentElement.style.cursor = 'auto';
    }
  }


  const resizeElem = document.createElement('div');
  resizeElem.setAttribute('style', `
    border: 1px dashed blue;
    display: flex;
    flex-flow: column nowrap;
    justify-content: space-between;
    position: absolute;
    width: 100%;
    height: 100%;
  `)
  isTextBox?resizeElem.addEventListener('dblclick', e=>elem.focus()):0;
  

  const floors = [
    document.createElement('div'),
    document.createElement('div'),
    document.createElement('div'),
  ];
  floors.forEach(elem => {
    elem.setAttribute('style', `
      display: flex;
      justify-content: space-between;
      width: 100%;
    `)
    resizeElem.append(elem);
  })

  for(let i=0; i<8; i++){
    const resizeBtn = document.createElement('div');
    resizeBtn.style.height = resizeBtn.style.width = '5px';
    resizeBtn.style.background = 'blue';

    if(i < 3){
      floors[0].append(resizeBtn)
      if(i===0) resizeBtn.style.transform = 'translate(-2.5px, -2.5px)';
      else if(i===2) resizeBtn.style.transform = 'translate(2.5px, -2.5px)';
      else resizeBtn.style.transform = 'translate(0, -2.5px)';
    }else if(i < 5){
      floors[1].append(resizeBtn)
      if(i===3) resizeBtn.style.transform = 'translate(-2.5px, 0)';
      else resizeBtn.style.transform = 'translate(2.5px, 0)';
    }else{
      floors[2].append(resizeBtn)
      if(i===5) resizeBtn.style.transform = 'translate(-2.5px, 2.5px)';
      else if(i===7) resizeBtn.style.transform = 'translate(2.5px, 2.5px)';
      else resizeBtn.style.transform = 'translate(0, 2.5px)';
    };
  }
  const circularElem = [floors[0].children[0], floors[0].children[1], floors[0].children[2], floors[1].children[1], floors[2].children[2], floors[2].children[1], floors[2].children[0], floors[1].children[0]];
  circularElem.forEach((btn, index) => {
    btn.style.cursor=circularDire[index];
    function mousedown(){
      document.addEventListener('mousemove', circularCombos[index])
      document.addEventListener('mouseup', e=>document.removeEventListener('mousemove', circularCombos[index], {once:true}))
    }
    mousedown.elem = btn;
    listeners.push(mousedown);
    btn.addEventListener('mousedown', mousedown);
  })
  container.append(rotater, resizeElem, elem);
}
let rotationDegree=0;
const resizeFunctions = {
  top(e, elem){
    e.stopPropagation();
    const height = getStyle(elem, 'height');
    const difference = getStyle(elem, 'top') - e.clientY;
    elem.style.top = e.clientY+'px';
    elem.style[(rotationDegree<112.5 && rotationDegree>67.5) || (rotationDegree<292.5 && rotationDegree>247.5)?'width':'height'] = (difference > 0 ? height + Math.abs(difference) : height - Math.abs(difference))+'px';
  },
  left(e, elem){
    e.stopPropagation();
    if(e.clientX > getStyle(sideBar, 'width')){
      const width = getStyle(elem, 'width');
      const difference = getStyle(elem, 'left') - e.clientX;
      elem.style.left = e.clientX+'px';
      elem.style[(rotationDegree<112.5 && rotationDegree>67.5) || (rotationDegree<292.5 && rotationDegree>247.5)?'height':'width'] = (difference > 0 ? width + Math.abs(difference) : width - Math.abs(difference))+'px';
    }
  },
  bottom(e, elem){
    e.stopPropagation();
    elem.style[(rotationDegree<112.5 && rotationDegree>67.5) || (rotationDegree<292.5 && rotationDegree>247.5)?'width':'height'] = (e.clientY - getStyle(elem, 'top'))+'px';
  },
  right(e, elem){
    e.stopPropagation();
    elem.style[(rotationDegree<112.5 && rotationDegree>67.5) || (rotationDegree<292.5 && rotationDegree>247.5)?'height':'width'] = (e.clientX - getStyle(elem, 'left'))+'px';
  }
}

const childAdded = lastElem => {
  const child = lastElem.cloneNode(true);

  const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
  svg.append(child);
  svg.style.height = svg.style.width = '100%';
  svg.style.backgroundColor = getComputedStyle(board).backgroundColor=='rgba(0, 0, 0, 0)'?'white':getComputedStyle(board).backgroundColor;
  svg.setAttribute('viewBox', `0 0 ${getStyle(board, 'width')} ${getStyle(board, 'height')}`);

  const container = document.createElement('div');
  container.classList.add('element');
  container.addEventListener('mouseover', e=>{
    lastElem.style.outline = '1px solid blue'
    lastElem.style.strokeWidth = getStyle(lastElem, 'stroke-width')+2+'px';
    lastElem.firstChild?lastElem.firstChild.style.fontWeight = getStyle(lastElem, 'font-weight')+200:0;
  });
  container.addEventListener('mouseout', e=>{
    lastElem.style.outline = 'none';
    lastElem.style.strokeWidth = getStyle(lastElem, 'stroke-width')-2+'px';
    lastElem.firstChild?lastElem.firstChild.style.fontWeight = getStyle(lastElem, 'font-weight')-200:0;
  });
  elements.append(container);

  const deleteBtn = document.createElement('div');
  deleteBtn.innerHTML = '&Cross;';
  deleteBtn.setAttribute('style', `
    height: 15px;
    width: 15px;
    font-size: 20px;
    position: relative;
    top: 10px;
    cursor: pointer;
  `)
  deleteBtn.title = 'Delete';
  deleteBtn.addEventListener('click', e => {
    lastElem.remove();
    container.remove();
  });

  container.append(deleteBtn, svg);
  elements.style.height = `${elements.scrollHeight + 10}px`;
  elements.scrollTop = elements.scrollHeight;
}
const select = elem => {
  for(let i=0; i<sideBar.children.length; i++){
    sideBar.children[i].style.backgroundColor='lightgray';
    sideBar.children[i].unselect?sideBar.children[i].unselect():0;
  }
  elem.style.backgroundColor = 'darkgray';
}



// Top Bar

const topBar = document.createElement('div');
topBar.classList.add('topBar');
topBar.setAttribute('style', `
  height: 50px;
  background: darkgray;
  border-bottom: 3px solid lightgray;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-right: 10%;
  padding-left: 7.5px;
  box-sizing: border-box;
`)

const wrapper = document.createElement('div');
wrapper.setAttribute('style', `
  display: flex;
  align-items: center;
`)

const clearBtn = document.createElement('button');
clearBtn.innerHTML = 'Clear';
clearBtn.addEventListener('click', e => board.innerHTML = elements.innerHTML='');

const importBtn = document.createElement('button');
importBtn.innerHTML = 'Import';
const fileBtn = document.createElement('input');
fileBtn.type = 'file';
importBtn.addEventListener('click', e => fileBtn.click());
fileBtn.addEventListener('input', e => {
  if(fileBtn.files[0].type === 'image/svg+xml'){
    const reader = new FileReader();
    reader.onload = e => {
      boardParent.innerHTML = e.target.result;
      board = boardParent.children[0]
      board.style.width = board.style.height = '100%';
      addListener(board)
      elements.innerHTML = '';
      for(let k=0; k<board.children.length; k++) childAdded(board.children[k]);
    }
    reader.readAsText(fileBtn.files[0]);
  }else{
    alert('Only svg files are allowed.');
  }
})

const exportBtn = document.createElement('button');
exportBtn.innerHTML = 'Export';


const anchor = document.createElement('a');
exportBtn.addEventListener('click', () => {
  anchor.setAttribute('href', 'data:text/plain;charset=utf-8, ' + encodeURIComponent(boardParent.innerHTML));
  anchor.setAttribute('download', 'sketch'+parseInt(Math.random()*10000)+'.svg')
  anchor.click();
})

const penWidth = document.createElement('input');
penWidth.type = 'number';
penWidth.min = penWidth.value = 1;
penWidth.max = 50;
penWidth.classList.add('pen-width');
penWidth.addEventListener('change', e => e.target.value === '' ? e.target.value = 1 : 0)

const colorMain = document.createElement('div');
colorMain.classList.add('color');
colorMain.title = 'Black';

const colorCont = document.createElement('div');
colorCont.setAttribute('style', `
  margin-top: 35px;
  margin-left: 50%;
  transform: translate(-50%, 0);
`)
colorCont.classList.add('container');

const colors = ['Brown', 'Red', 'Green', 'Black', 'Blue', 'Yellow', 'Pink', 'Purple', 'Orange', 'Skyblue'];
colors.forEach(elem => {
  const color = document.createElement('div');
  color.style.backgroundColor = color.title = elem;
  color.style.margin = '2.5px';
  color.addEventListener('click', e => {
    colorMain.style.backgroundColor = colorMain.title = elem;
    colorCont.style.display = 'none';
  })
  colorCont.append(color);
})

colorCont.addEventListener('focusout', e => colorCont.style.display = 'none');
colorMain.addEventListener('click', e => !colorCont.contains(e.target)?colorCont.style.display = 'flex':0);

colorMain.append(colorCont)
wrapper.append(colorMain, penWidth, clearBtn, importBtn, exportBtn);

topBar.append(wrapper);
document.body.append(topBar);


// Side Bar

const sideBar = document.createElement('div');
sideBar.classList.add('sideBar');
sideBar.setAttribute('style', `
  width: 50px;
  height: ${window.innerHeight-50}px;
  background: lightgray;
  display: flex;
  justify-content: center;
  align-items: center;
  flex-flow: column wrap;
`)

const penBtn = document.createElement('div');
penBtn.title = 'Pen';
penBtn.style.backgroundImage = 'url("Pictures/Pen.png")';
penBtn.unselect = e => removeListener(board);
penBtn.addEventListener('click', e => {
  select(penBtn);
  addListener(board);
});

const textBtn = document.createElement('div');
textBtn.title = 'Add textbox.';
textBtn.style.backgroundImage = 'url("Pictures/Text-cursor.png")';
textBtn.unselect = () => {
  textBtn.addEventListener('click', e => {
    select(textBtn);
    box();
  }, {once:true})
}
const box = e => {
  const textbox = document.createElement('textarea');
  textbox.setAttribute('style', `
    width: 100%;
    height: 100%;
    background: transparent;
    outline: none;
    border: none;
    resize: none;
  `)
  textbox.addEventListener('scroll', e => {
    textbox.style.height = textbox.scrollHeight+'px';
  })
  resize(textbox, true);

  const textAdder = e => {
    if(!has(textbox.parentElement.getElementsByTagName('*'), e.target)){
      if(textbox.value.trim() !== ''){
        const style = getComputedStyle(textbox);
        const object = document.createElementNS("http://www.w3.org/2000/svg", 'foreignObject');
        const textElem = document.createElement('div');
        textElem.innerHTML = textbox.value;
        object.setAttribute('x', (getStyle(textbox.parentElement, 'left')-getStyle(sideBar, 'width')+2)+'px');
        object.setAttribute('y', (getStyle(textbox.parentElement, 'top')-getStyle(topBar, 'height')+2)+'px');
        object.setAttribute('style', `
          height: ${textbox.scrollHeight}px;
          width: ${style.width};
          overflow: visible;
        `)

        textElem.setAttribute('style', `
          font-size: ${style.fontSize};
          color: ${style.color};
          font-family: ${style.fontFamily};
          transform: ${textbox.parentElement.style.transform};
          width: 100%;
          height: 100%;
          overflow-wrap: break-word;
          -webkit-user-select: none;
          -moz-user-select: none;
          -ms-user-select: none;
          user-select: none;
        `);
        object.append(textElem)
        board.append(object);
        childAdded(object);
      }
      textbox.parentElement.remove();
      document.removeEventListener('mousedown', textAdder)
    }
  }

  document.addEventListener('mousedown', textAdder)
  boardParent.prepend(textbox.parentElement);
  textbox.focus()
}


const lineBtn = document.createElement('div');
lineBtn.title = 'Line';
lineBtn.style.backgroundImage = 'url("Pictures/zigZag.png")';
lineBtn.unselect = e => {
  discard.click();
  lineBtn.addEventListener('click', e => {
    let points = [], coordinates = [];
    select(lineBtn);
    removeListener(board);
  
    const line = document.createElementNS('http://www.w3.org/2000/svg', 'polyline');
    line.setAttribute('style', `
      fill: transparent;
      stroke-linejoin: round;
      stroke-linecap: round;
      stroke: ${getComputedStyle(colorMain).backgroundColor};
      stroke-width: ${penWidth.value+'px'};
    `)
    line.setAttribute('points', '');
  
    const downFunc = e => {
      if(e.target !== save && e.target !== discard && e.clientX > getStyle(sideBar, 'width') && e.clientX < window.innerWidth-getStyle(rightSideBar, 'width') && e.clientY > getStyle(topBar, 'height')){
        const currPoint = `${e.clientX-getStyle(sideBar, 'width')},${e.clientY - getStyle(topBar, 'height')} `;
        const prevPoints = line.getAttribute('points');
        line.setAttribute('points', prevPoints+currPoint);
  
        const adjustPoints = e => {
          styleString = `
            left: ${e.clientX}px;
            top: ${e.clientY}px;
          `;
          point.setAttribute('style', styleString);
          coordinate.innerHTML = `Left: ${e.clientX} \nTop: ${e.clientY}`;
          coordinate.setAttribute('style', styleString)
        }
        let styleString = `
          left: ${e.clientX}px;
          top: ${e.clientY}px;
        `;
        const point = document.createElement('div');
        point.classList.add('point');
        point.setAttribute('style', styleString);
        point.addEventListener('mousedown', e => {
          e.preventDefault();
          const pointMoveFunc = e => {
            const pointIndex = points.indexOf(point);
            const pointsArr = line.getAttribute('points').split(' ');
            pointsArr.splice(pointIndex, 1, `${e.clientX-getStyle(sideBar, 'width')},${e.clientY - getStyle(topBar, 'height')}`);
            line.setAttribute('points', pointsArr.join(' '));
            adjustPoints(e);
          }
          document.addEventListener('mousemove', pointMoveFunc);
          document.addEventListener('mouseup', e=>document.removeEventListener('mousemove', pointMoveFunc), {once:true});
        })
        point.addEventListener('dblclick', e => {
          const newPoints = line.getAttribute('points').split(' ');
          newPoints.splice(points.indexOf(point), 1);
          line.setAttribute('points', newPoints.join(' '));
  
          points.splice(points.indexOf(point), 1);
          coordinates.splice(coordinates.indexOf(coordinate), 1);
          
          point.remove();
          coordinate.remove();
        })
        points.push(point);
  
        const coordinate = document.createElement('pre');
        coordinate.classList.add('coordinates');
        coordinate.innerHTML = `Left: ${e.clientX} \nTop: ${e.clientY}`;
        coordinate.setAttribute('style', styleString)
  
        document.body.append(point, coordinate);
        coordinates.push(coordinate);
  
        const moveFunc = e => {
          line.setAttribute('points', prevPoints+`${e.clientX-getStyle(sideBar, 'width')},${e.clientY - getStyle(topBar, 'height')} `)
          adjustPoints(e);
        };
        document.addEventListener('mousemove', moveFunc)
        document.addEventListener('mouseup', e=>document.removeEventListener('mousemove', moveFunc), {once:true});
      }
    }
    board.addEventListener('mousedown', downFunc);
    popUp.style.display = 'flex';
    function rem(){
      coordinates.forEach(elem => elem.remove());
      points.forEach(elem => elem.remove());
      board.removeEventListener('mousedown', downFunc);
    }
    popUp.save(e => {
      rem();
      childAdded(line);
    })
    popUp.discard(e => {
      rem();
      line.remove();
    })
  
    board.append(line)
  }, {once:true});
}

const eraser = document.createElement('div');
eraser.title = 'Eraser';
eraser.style.backgroundImage = 'url("Pictures/eraser.png")';
let isEraser;
eraser.unselect = e => {
  isEraser = null;
  removeListener(board);
  eraser.addEventListener('click', e => {
    select(eraser);
    addListener(board);
    isEraser = 'white';
  }, {once:true});
}

const imageBtn = document.createElement('div');
imageBtn.title = 'Insert an Image.';
imageBtn.style.backgroundImage = 'url("Pictures/Image-icon.webp")';
imageBtn.addEventListener('click', e => {
  select(imageBtn);
  imageInput.click();
})
const imageInput = document.createElement('input');
imageInput.type = 'file';
imageInput.accept = 'image/png, image/jpeg, image/svg+xml';
imageInput.addEventListener('input', e => {
  const image = document.createElement('img');
  image.src = URL.createObjectURL(e.target.files[0]);
  image.setAttribute('style', `
    height: 100%;
    width: 100%;
  `)
  resize(image, false);
  board.parentElement.prepend(image.parentElement);
  e.target.value = '';

  const addImage = e => {
    if(!has(image.parentElement.querySelectorAll('*'), e.target)){
      const style = getComputedStyle(image.parentElement);
      const svgImage = document.createElementNS('http://www.w3.org/2000/svg', 'image');
      svgImage.setAttribute('y', (getStyle(image.parentElement, 'top')-getStyle(topBar, 'height')+2)+'px');
      svgImage.setAttribute('x', (getStyle(image.parentElement, 'left')-getStyle(sideBar, 'width')+2)+'px');
      svgImage.setAttribute('href', image.src);

      const degree = image.parentElement.style.transform.replace('rotate', '').replace('(', '').replace(')', '').replace('deg', '');
      svgImage.setAttribute('transform', `rotate(${degree?degree:0}, ${Number(svgImage.getAttribute('x').replace('px', ''))+getStyle(image.parentElement, 'width')/2}, ${Number(svgImage.getAttribute('y').replace('px', ''))+getStyle(image.parentElement, 'height')/2})`)
      svgImage.setAttribute('preserveAspectRatio', 'none');
      svgImage.setAttribute('style', `
        height: ${style.height};
        width: ${style.width};
      `);
      svgImage.setAttribute('height', style.height);
      svgImage.setAttribute('width', style.width);
      image.parentElement.remove();
      board.append(svgImage);
      document.removeEventListener('mousedown', addImage);
      childAdded(svgImage);
    }
  }

  document.addEventListener('mousedown', addImage);
})

const laserBtn = document.createElement('div');
laserBtn.title = 'Laser';
laserBtn.style.backgroundImage = 'url("Pictures/Laser.png")';
laserBtn.style.backgroundSize = '35px 15px';
let isLaser;
laserBtn.unselect = e => {
  isLaser=null;
  laserBtn.addEventListener('click', e => {
    select(laserBtn);
    isLaser='red';
    addListener(board);
  }, {once:true});
};

const laserUp = e => {
  board.removeEventListener('mousemove', drawFunc);
  const elem = board.lastChild;
  elem.style.animation = 'laser 1s 1 forwards';
  setTimeout(e => {
    elem.remove();
  }, 600);
}

sideBar.append(penBtn, textBtn, lineBtn, eraser, imageBtn, laserBtn);


// Right Side Bar

const rightSideBar = document.createElement('div');
rightSideBar.setAttribute('style', `
  height: ${window.innerHeight-50}px;
  width: 210px;
  padding-top: 10px;
  padding-left: 10px;
  background: lightgray;
  display: flex;
  flex-flow: column nowrap;
  box-sizing: border-box;
`)

const heading = document.createElement('div');
heading.setAttribute('style', `
  display: flex;
  justify-content: center;
  align-items: flex-start;
  font-size: 20px;
  height: 25px;
  width: 100%;
`)
heading.innerHTML = 'Elements';

const elements = document.createElement('div');
elements.setAttribute('style', `
  width: 100%;
  overflow-y: auto;
`)
elements.classList.add('elementContainer');

rightSideBar.append(heading, elements);


// Board

const boardParent = document.createElement('div');
boardParent.style.height = `${window.innerHeight - getStyle(topBar, 'height')}px`;
boardParent.style.width = `${window.innerWidth - getStyle(sideBar, 'width')-getStyle(rightSideBar, 'width')}px`;

let board = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
board.classList.add('board');

const drawFunc = e => {
  if(board.lastChild.tagName == 'polyline'){
    const currPoint = `${e.clientX-getStyle(sideBar, 'width')},${e.clientY - getStyle(topBar, 'height')} `;
    const prevPoints = board.lastChild.getAttribute('points');
    board.lastChild.setAttribute('points', prevPoints+currPoint);
  }
}

const downFunc = e => {
  const line = document.createElementNS('http://www.w3.org/2000/svg', 'polyline');
  line.setAttribute('style', `
    fill: transparent;
    stroke-linejoin: round;
    stroke-linecap: round;
    stroke: ${isLaser ?? isEraser ?? getComputedStyle(colorMain).backgroundColor};
    stroke-width: ${penWidth.value+'px'};
  `)
  line.setAttribute('points', '');
  board.addEventListener('mousemove', drawFunc)
  !isLaser?document.addEventListener('mouseup', upFunc, {once:true}):document.addEventListener('mouseup', laserUp, {once:true});
  board.append(line);
}

const upFunc = e => {
  board.removeEventListener('mousemove', drawFunc);
  if(board.lastChild.tagName == 'polyline'){
    const points = board.lastChild?.getAttribute('points')?.trim();
    if(points && points.split(' ').length>1) isEraser ?? childAdded(board.lastChild);
    else board.lastChild.remove();
  }
}

const addListener = elem => elem.addEventListener('mousedown', downFunc);
const removeListener = elem => elem.removeEventListener('mousedown', downFunc)



// Other

boardParent.append(board);
const paarent = document.createElement('div');
paarent.style.display = 'flex';
paarent.style.flexShrink = '0';

paarent.append(sideBar, boardParent, rightSideBar);

const popUp = document.createElement('div');
popUp.setAttribute('style', `
  height: 70px;
  width: 100%;
  position: absolute;
  bottom: 0;
  display: none;
  justify-content: center;
`)

const discard = document.createElement('div');
discard.style.background = 'red';
discard.classList.add('popUpButton');
discard.innerHTML = '&cross;';

const save = document.createElement('div');
save.style.background = 'lightgreen';
save.classList.add('popUpButton');
save.innerHTML = '&check;';

let saveListener, discardListener;
popUp.discard = func => {
  discardListener = e => {popUp.style.display = 'none';save.removeEventListener('click', saveListener);func()}
  discard.addEventListener('click', discardListener, {once:true});
}
popUp.save = func => {
  saveListener = e => {popUp.style.display = 'none';discard.removeEventListener('click', discardListener);func()};
  save.addEventListener('click', saveListener, {once:true});
}

popUp.append(discard, save);
penBtn.click();


document.body.append(paarent, popUp);
    </script>
  </body>
</html>
